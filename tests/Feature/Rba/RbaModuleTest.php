<?php

namespace Tests\Feature\Rba;

use App\Models\Rba\RbaDetail;
use App\Models\Rba\RbaIndikator;
use App\Models\Rba\RbaJenis;
use App\Models\Rba\RbaKategori;
use App\Models\Rba\RbaTahunAnggaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbaModuleTest extends TestCase
{
    use RefreshDatabase;

    private function seedHierarchy(): RbaDetail
    {
        $jenis = RbaJenis::create(['kode_jenis' => 'J1', 'uraian' => 'Jenis 1']);
        $kategori = RbaKategori::create(['jenis_id' => $jenis->id, 'kode_kategori' => 'K1', 'uraian' => 'Kategori 1']);
        $indikator = RbaIndikator::create(['kategori_id' => $kategori->id, 'kode_indikator' => 'I1', 'uraian' => 'Indikator 1']);

        return RbaDetail::create(['indikator_id' => $indikator->id, 'kode_detail' => 'D1', 'uraian' => 'Detail 1']);
    }

    private function loginAs(string $role = 'admin'): User
    {
        $user = User::factory()->create(['role' => $role]);
        $this->actingAs($user);
        // Route RBA kini memakai middleware grup `web` (seperti route lama),
        // sehingga request POST/PUT/DELETE stateful butuh token CSRF.
        // Di test, nonaktifkan verifikasi CSRF agar fokus ke logika modul.
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        return $user;
    }

    public function test_master_crud_records_history(): void
    {
        $this->loginAs();

        $this->postJson(route('rba.master.jenis.store'), ['kode_jenis' => 'J1', 'uraian' => 'Jenis 1'])
            ->assertOk()->assertJson(['success' => true]);
        $jenis = RbaJenis::where('kode_jenis', 'J1')->first();
        $this->assertNotNull($jenis);

        $this->assertDatabaseHas('rba_history', ['aksi' => 'INSERT', 'tabel_referensi' => 'rba_jenis', 'referensi_id' => $jenis->id]);

        $this->putJson(route('rba.master.jenis.update', $jenis->id), ['kode_jenis' => 'J1', 'uraian' => 'Jenis 1 ubah'])
            ->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('rba_history', ['aksi' => 'UPDATE', 'tabel_referensi' => 'rba_jenis', 'referensi_id' => $jenis->id]);
    }

    public function test_target_needs_existing_detail_and_year(): void
    {
        $this->loginAs();
        $detail = $this->seedHierarchy();
        $tahun = RbaTahunAnggaran::create(['tahun' => 2026, 'status' => 'OPEN', 'is_current' => true]);

        $response = $this->postJson(route('rba.transaksi.target.save'), [
            'tahun_anggaran_id' => $tahun->id,
            'targets' => [['detail_id' => $detail->id, 'nilai' => 1000000]],
        ]);
        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('rba_target', ['detail_id' => $detail->id, 'tahun_anggaran_id' => $tahun->id, 'nilai' => 1000000]);
        $this->assertDatabaseHas('rba_history', ['aksi' => 'INSERT', 'tabel_referensi' => 'rba_target']);
    }

    public function test_target_saves_current_and_next_year_together(): void
    {
        $this->loginAs();
        $detail = $this->seedHierarchy();
        $tahun = RbaTahunAnggaran::create(['tahun' => 2026, 'status' => 'OPEN', 'is_current' => true]);
        $next = RbaTahunAnggaran::create(['tahun' => 2027, 'status' => 'OPEN']);

        $response = $this->postJson(route('rba.transaksi.target.save'), [
            'tahun_anggaran_id' => $tahun->id,
            'targets' => [['detail_id' => $detail->id, 'nilai' => 1000000]],
            'next_tahun_anggaran_id' => $next->id,
            'next_targets' => [['detail_id' => $detail->id, 'nilai' => 2000000]],
        ]);
        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('rba_target', ['detail_id' => $detail->id, 'tahun_anggaran_id' => $tahun->id, 'nilai' => 1000000]);
        $this->assertDatabaseHas('rba_target', ['detail_id' => $detail->id, 'tahun_anggaran_id' => $next->id, 'nilai' => 2000000]);
    }

    public function test_locked_year_rejects_operator_and_requires_admin_reason(): void
    {
        $detail = $this->seedHierarchy();
        $tahun = RbaTahunAnggaran::create(['tahun' => 2026, 'status' => 'LOCKED']);

        // Non-admin ditolak
        $this->loginAs('operator');
        $this->postJson(route('rba.transaksi.target.save'), [
            'tahun_anggaran_id' => $tahun->id,
            'targets' => [['detail_id' => $detail->id, 'nilai' => 100]],
        ])->assertStatus(422);

        // Admin tanpa alasan ditolak
        $this->loginAs('admin');
        $this->postJson(route('rba.transaksi.target.save'), [
            'tahun_anggaran_id' => $tahun->id,
            'targets' => [['detail_id' => $detail->id, 'nilai' => 100]],
        ])->assertStatus(422);

        // Admin dengan alasan tercatat sebagai OVERRIDE
        $this->postJson(route('rba.transaksi.target.save'), [
            'tahun_anggaran_id' => $tahun->id,
            'targets' => [['detail_id' => $detail->id, 'nilai' => 100]],
            'alasan' => 'Revisi pagu disetujui direktur',
        ])->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('rba_history', ['aksi' => 'OVERRIDE', 'tabel_referensi' => 'rba_target']);
    }

    public function test_realisasi_percentage_calculation(): void
    {
        $this->loginAs();
        $detail = $this->seedHierarchy();
        $tahun = RbaTahunAnggaran::create(['tahun' => 2026, 'status' => 'OPEN', 'is_current' => true]);

        $this->postJson(route('rba.transaksi.target.save'), [
            'tahun_anggaran_id' => $tahun->id,
            'targets' => [['detail_id' => $detail->id, 'nilai' => 1000]],
        ])->assertOk();
        $targetId = \App\Models\Rba\RbaTarget::first()->id;

        $this->postJson(route('rba.transaksi.realisasi.save'), [
            'tahun_anggaran_id' => $tahun->id,
            'rows' => [[
                'target_id' => $targetId,
                'semester_1' => 300,
                'semester_2' => 200,
                'keterangan' => 'OK',
            ]],
        ])->assertOk()->assertJson(['success' => true]);

        $data = $this->getJson(route('rba.transaksi.realisasi.data', ['tahun_anggaran_id' => $tahun->id]))
            ->assertOk()->assertJson(['success' => true])->json('data');
        $this->assertSame(500.0, (float) $data[0]['total']);
        $this->assertSame(50.0, (float) $data[0]['persentase']);
    }

    public function test_report_aggregates_with_simple_sum(): void
    {
        $this->loginAs();
        $detail = $this->seedHierarchy();
        $tahun = RbaTahunAnggaran::create(['tahun' => 2026, 'status' => 'OPEN', 'is_current' => true]);

        $this->postJson(route('rba.transaksi.target.save'), [
            'tahun_anggaran_id' => $tahun->id,
            'targets' => [['detail_id' => $detail->id, 'nilai' => 2000]],
        ])->assertOk();
        $targetId = \App\Models\Rba\RbaTarget::first()->id;
        $this->postJson(route('rba.transaksi.realisasi.save'), [
            'tahun_anggaran_id' => $tahun->id,
            'rows' => [['target_id' => $targetId, 'semester_1' => 500, 'semester_2' => 500]],
        ])->assertOk();

        $report = $this->getJson(route('rba.laporan.data', ['tahun_anggaran_id' => $tahun->id, 'mode' => 'tahunan']))
            ->assertOk()->assertJson(['success' => true])->json();
        $this->assertSame(2000.0, (float) $report['rekap']['target']);
        $this->assertSame(1000.0, (float) $report['rekap']['realisasi']);
        $this->assertSame(50.0, (float) $report['rekap']['persentase']);
        $this->assertCount(1, $report['rows']);
        $this->assertCount(1, $report['rekap_jenis']);
    }

    public function test_feature_flag_disables_module(): void
    {
        config(['rba.enabled' => false]);
        $this->loginAs();

        $this->get(route('rba.transaksi.target'))->assertStatus(404);
    }

    public function test_pages_render_for_admin(): void
    {
        $this->loginAs();

        foreach ([
            route('rba.master.jenis.index'),
            route('rba.master.kategori.index'),
            route('rba.master.indikator.index'),
            route('rba.master.detail.index'),
            route('rba.master.tahun.index'),
            route('rba.transaksi.target'),
            route('rba.transaksi.realisasi'),
            route('rba.laporan.index'),
            route('rba.laporan.history'),
            route('dashboard'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_missing_tables_returns_explicit_503_not_silent_redirect(): void
    {
        $this->loginAs();
        foreach (['rba_history', 'rba_realisasi', 'rba_target', 'rba_tahun_anggaran', 'rba_detail', 'rba_indikator', 'rba_kategori', 'rba_jenis'] as $table) {
            \Illuminate\Support\Facades\Schema::dropIfExists($table);
        }

        $this->get(route('rba.master.jenis.index'))->assertStatus(503);
        $this->get(route('rba.transaksi.target'))->assertStatus(503);
        $this->get(route('rba.laporan.index'))->assertStatus(503);
    }
}
