window.ajaxurl = settings.ajax_url;
(function ($) {
  /**
   * check currently set attribute values
   * * function for 'woocommerce search customization'
   */
  $("input.wc-custom--filter").each(function (i, obj) {
    const isChecked = $(obj).closest("li").hasClass("chosen");
    if (isChecked) {
      $(obj).prop("checked", true);
    }
  });

  /**
   * add goto filter query url effect to custom checkbox
   * * function for 'woocommerce search customization'
   */
  $("input.wc-custom--filter").click(function (_) {
    const link = $(this).data("link");
    window.location.replace(link);
  });

  /**
   * check if it is tablet or not
   */
  if (
    /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
      navigator.userAgent
    )
  ) {
    $(".headerTop li.extra-menu-item").hide();
    $(".headerTop li.extra-menu-item-touch").show();
  } else {
    $(".headerTop li.extra-menu-item").show();
    $(".headerTop li.extra-menu-item-touch").hide();
  }

  /**
   * * check ajax wishlist counter & cart counter container
   * * and update the count
   */
  $(".ajax-counter-list").on("DOMSubtreeModified", function () {
    const cartCount = parseInt($(this).find("#mini-item-counter").text());
    const wishlistCount = parseInt(
      $(this).find("span.yith-wcwl-items-count").text()
    );

    if (!Number.isNaN(cartCount) && !Number.isNaN(wishlistCount)) {
      // extra-menu-item menu-item-wishlist
      // extra-menu-item menu-item-cart mini-cart woocommerce
      $("li.extra-menu-item.menu-item-cart span.mini-item-counter").text(
        cartCount
      );
      $("li.extra-menu-item.menu-item-wishlist span.mini-item-counter").text(
        wishlistCount
      );
    }
  });
})(jQuery);
