$(function () {
	$(".sidebarMenuScroll").overlayScrollbars({
		scrollbars: {
			visibility: "auto",
			autoHide: "scroll",
			autoHideDelay: 200,
			dragScrolling: true,
			clickScrolling: false,
			touchSupport: true,
			snapHandle: false,
		},
	});

	// Auto-scroll sidebar ke menu aktif agar langsung terlihat
	// tanpa perlu scroll manual. Dijalankan setelah OverlayScrollbars
	// selesai init (viewport .os-viewport sudah ada).
	setTimeout(scrollSidebarToActiveMenu, 50);
});

function scrollSidebarToActiveMenu() {
	var $container = $(".sidebarMenuScroll");
	var $active = $container.find("li.active").first();
	if (!$active.length) {
		return;
	}

	try {
		// Cara 1: API OverlayScrollbars v1 (scroll ke elemen, posisi tengah).
		var instance = window.OverlayScrollbars
			? window.OverlayScrollbars($container[0])
			: null;
		if (instance && typeof instance.scroll === "function") {
			instance.scroll(
				{ el: $active[0], scroll: { x: "never", y: "ifneeded" }, block: { y: "center" } },
				0
			);
			return;
		}
	} catch (e) {
		// Abaikan, lanjut ke fallback di bawah.
	}

	// Cara 2: fallback viewport asli OverlayScrollbars (.os-viewport).
	// Posisi dihitung via getBoundingClientRect agar akurat
	// apa pun kedalaman DOM item aktif di dalam konten scroll.
	var viewport = $container.find(".os-viewport").first()[0] || $container[0];
	var viewportRect = viewport.getBoundingClientRect();
	var itemRect = $active[0].getBoundingClientRect();
	viewport.scrollTop =
		viewport.scrollTop +
		(itemRect.top - viewportRect.top) -
		viewport.clientHeight / 2 +
		itemRect.height / 2;
}

// Scroll 290
$(function () {
	$(".scroll290").overlayScrollbars({
		scrollbars: {
			visibility: "auto",
			autoHide: "scroll",
			autoHideDelay: 200,
			dragScrolling: true,
			clickScrolling: false,
			touchSupport: true,
			snapHandle: false,
		},
	});
});

// Scroll 300
$(function () {
	$(".scroll300").overlayScrollbars({
		scrollbars: {
			visibility: "auto",
			autoHide: "scroll",
			autoHideDelay: 200,
			dragScrolling: true,
			clickScrolling: false,
			touchSupport: true,
			snapHandle: false,
		},
	});
});

// Scroll 350
$(function () {
	$(".scroll350").overlayScrollbars({
		scrollbars: {
			visibility: "auto",
			autoHide: "scroll",
			autoHideDelay: 200,
			dragScrolling: true,
			clickScrolling: false,
			touchSupport: true,
			snapHandle: false,
		},
	});
});
