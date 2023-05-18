(function ($) {
  const cpvProductView = function () {
    $(".footer-btns").on("click", ".mf-product-quick-view", function (e) {
      e.preventDefault();
      const $modal = $(document.getElementById("mf-quick-view-modal"));
      const $product = $modal.find(".product-modal-content");

      const $a = $(this);
      const id = $a.data("id");

      $product.hide().html("");
      $modal.addClass("loading").removeClass("loaded");
      // modal open
      $modal.fadeIn();
      $modal.addClass("open");

      const data = {
        nonce: viewParams.nonce,
        product_id: id,
      };
      const ajax_url = viewParams.wc_ajax_url
        .toString()
        .replace("%%endpoint%%", "product_quick_view");

      $.post(ajax_url, data, function (response) {
        $product.show().append(response.data);
        $modal.removeClass("loading").addClass("loaded");
        const $gallery = $product.find(".woocommerce-product-gallery"),
          $variation = $(".variations_form"),
          $buttons = $product.find("form.cart .actions-button"),
          $buy_now = $buttons.find(".buy_now_button");
        $gallery.removeAttr("style");
        $gallery.find("img.lazy").lazyload().trigger("appear");

        $gallery.imagesLoaded(function () {
          $gallery
            .find(".woocommerce-product-gallery__wrapper")
            .not(".slick-initialized")
            .slick({
              slidesToShow: 1,
              slidesToScroll: 1,
              infinite: false,
              prevArrow:
                '<span class="icon-chevron-left slick-prev-arrow"></span>',
              nextArrow:
                '<span class="icon-chevron-right slick-next-arrow"></span>',
            });
        });

        $product.find("div.product").addClass("qv-modal");

        if ($buy_now.length > 0) {
          $buttons.prepend($buy_now);
        }

        $gallery
          .find(".woocommerce-product-gallery__image")
          .on("click", function (e) {
            e.preventDefault();
          });

        if (typeof wc_add_to_cart_variation_params !== "undefined") {
          $variation.each(function () {
            $(this).wc_variation_form();
          });
        }

        if (typeof $.fn.tawcvs_variation_swatches_form !== "undefined") {
          $variation.tawcvs_variation_swatches_form();
        }

        productVatiation();
        if (typeof tawcvs !== "undefined") {
          if (tawcvs.tooltip === "yes") {
            $variation.find(".swatch").tooltip({
              classes: { "ui-tooltip": "martfury-tooltip" },
              tooltipClass: "martfury-tooltip qv-tool-tip",
              position: { my: "center bottom", at: "center top-13" },
              create: function () {
                $(".ui-helper-hidden-accessible").remove();
              },
            });
          }
        }

        $product.find(".compare").tooltip({
          content: function () {
            return $(this).html();
          },
          classes: { "ui-tooltip": "martfury-tooltip" },
          tooltipClass: "martfury-tooltip qv-tooltip",
          position: { my: "center bottom", at: "center top-13" },
          create: function () {
            $(".ui-helper-hidden-accessible").remove();
          },
        });

        $product.find("[data-rel=tooltip]").tooltip({
          classes: { "ui-tooltip": "martfury-tooltip" },
          tooltipClass: "martfury-tooltip qv-tooltip",
          position: { my: "center bottom", at: "center top-13" },
          create: function () {
            $(".ui-helper-hidden-accessible").remove();
          },
        });
      });
    });

    $(document).on(
      "click",
      "#mf-quick-view-modal .close-modal, #mf-quick-view-modal .mf-modal-overlay",
      function (e) {
        const $modal = $(document.getElementById("mf-quick-view-modal"));
        e.preventDefault();

        $modal.fadeOut(function () {
          $(this).removeClass("open");
        });
      }
    );
  };

  const productVatiation = () => {
    $("body").on("tawcvs_initialized", function () {
      $(".variations_form").unbind("tawcvs_no_matching_variations");
      $(".variations_form").on(
        "tawcvs_no_matching_variations",
        function (event, $el) {
          event.preventDefault();

          $(".variations_form")
            .find(".woocommerce-variation.single_variation")
            .show();
          if (typeof wc_add_to_cart_variation_params !== "undefined") {
            $(".variations_form")
              .find(".single_variation")
              .slideDown(200)
              .html(
                "<p>" +
                  wc_add_to_cart_variation_params.i18n_no_matching_variations_text +
                  "</p>"
              );
          }
        }
      );
    });

    $(".variations_form").on(
      "found_variation.wc-variation-form",
      function (event, variation) {
        var $sku = $(".mf-product-detail")
          .find(".meta-sku")
          .find(".meta-value");

        if (typeof $sku.wc_set_content !== "function") {
          return;
        }

        if (typeof $sku.wc_reset_content !== "function") {
          return;
        }

        if (variation.sku) {
          $sku.wc_set_content(variation.sku);
        } else {
          $sku.wc_reset_content();
        }
      }
    );

    $(".variations_form td.value")
      .find("select")
      .each(function () {
        if ($(this).parent().hasClass("wcboost-variation-swatches")) {
          return;
        }
        $(this)
          .on("change", function () {
            var value = $(this).find("option:selected").text();
            $(this).closest("tr").find("td.label .mf-attr-value").html(value);
          })
          .trigger("change");
      });

    buyNow();
    addToCartAjax();
    $(document.body).trigger("yith_wcwl_init");
    $(document.body).trigger("init_variation_swatches");
  };

  const buyNow = function () {
    if (!$("body").find(".mf-single-product").hasClass("mf-has-buy-now")) {
      return;
    }
    $("body")
      .find("form.cart")
      .on("click", ".buy_now_button", function (e) {
        e.preventDefault();
        var $form = $(this).closest("form.cart"),
          is_disabled = $(this).is(":disabled");

        if (is_disabled) {
          jQuery("html, body").animate(
            {
              scrollTop: $(this).offset().top - 200,
            },
            900
          );
        } else {
          $form.append('<input type="hidden" value="true" name="buy_now" />');
          $form.find(".single_add_to_cart_button").addClass("has-buy-now");
          $form.find(".single_add_to_cart_button").trigger("click");
        }
      });
    var $variations_form = $(".variations_form");
    $variations_form.on("hide_variation", function (event) {
      event.preventDefault();
      $variations_form
        .find(".buy_now_button")
        .addClass("disabled wc-variation-selection-needed");
    });

    $variations_form.on(
      "show_variation",
      function (event, variation, purchasable) {
        event.preventDefault();
        if (purchasable) {
          $variations_form
            .find(".buy_now_button")
            .removeClass("disabled wc-variation-selection-needed");
        } else {
          $variations_form
            .find(".buy_now_button")
            .addClass("disabled wc-variation-selection-needed");
        }
      }
    );
  };

  const addToCartAjax = function () {
    if (viewParams.add_to_cart_ajax == "0") {
      return;
    }

    var found = false;
    $("body")
      .find("form.cart")
      .on("click", ".single_add_to_cart_button", function (e) {
        const $el = $(this),
          $cartForm = $el.closest("form.cart"),
          $productTitle = $el.closest(".entry-summary").find(".product_title");

        if ($el.hasClass("has-buy-now")) {
          return;
        }

        if ($cartForm.length > 0) {
          e.preventDefault();
        } else {
          return;
        }

        if ($el.hasClass("disabled")) {
          return;
        }

        $el.addClass("loading");
        if (found) {
          return;
        }
        found = true;

        $cartForm.find('input[name="buy_now"]').remove();

        var formdata = $cartForm.serializeArray(),
          currentURL = window.location.href;

        if ($el.val() != "") {
          formdata.push({ name: $el.attr("name"), value: $el.val() });
        }
        $.ajax({
          url: window.location.href,
          method: "post",
          data: formdata,
          error: function () {
            window.location = currentURL;
          },
          success: function (response) {
            if (!response) {
              window.location = currentURL;
            }

            if (typeof wc_add_to_cart_params !== "undefined") {
              if (wc_add_to_cart_params.cart_redirect_after_add === "yes") {
                window.location = wc_add_to_cart_params.cart_url;
                return;
              }
            }

            $(document.body).trigger("updated_wc_div");
            $(document.body).on("wc_fragments_refreshed", function () {
              $el.removeClass("loading");
            });

            var $message = "",
              className = "success",
              $content = false;
            if ($(response).find(".woocommerce-message").length > 0) {
              $message = $(response).find(".woocommerce-message").html();
            }

            if ($(response).find(".woocommerce-error").length > 0) {
              $message = $(response).find(".woocommerce-error").html();
              className = "error";
            }

            if ($(response).find(".woocommerce-info").length > 0) {
              $message = $(response).find(".woocommerce-info").html();
            }

            martfury.addedToCartNotice($message, true, className, false);

            found = false;
          },
        });
      });
  };

  const productQuantity = function () {
    $("body").on(
      "click",
      ".quantity .increase, .quantity .decrease",
      function (e) {
        e.preventDefault();

        var $this = $(this),
          $qty = $this.siblings(".qty"),
          current = 0,
          min = parseFloat($qty.attr("min")),
          max = parseFloat($qty.attr("max")),
          step = parseFloat($qty.attr("step"));

        if ($qty.val() !== "") {
          current = parseFloat($qty.val());
        } else if ($qty.attr("placeholder") !== "") {
          current = parseFloat($qty.attr("placeholder"));
        }

        min = min ? min : 0;
        max = max ? max : current + 1;

        if ($this.hasClass("decrease") && current > min) {
          $qty.val(current - step);
          $qty.trigger("change");
        }
        if ($this.hasClass("increase") && current < max) {
          $qty.val(current + step);
          $qty.trigger("change");
        }
      }
    );
  };

  const addCompare = () => {
    const $compareCounter = $(".siteHeader").find("#mini-compare-counter");

    $("body").on("click", "a.compare.added", function (e) {
      e.preventDefault();
      $(".siteHeader").find(".yith-woocompare-open").trigger("click");
    });

    $("body").on("click", "a.compare:not(.added)", function (e) {
      e.preventDefault();

      var $el = $(this);

      $el.addClass("loading");
      $.ajax({
        url: $el.attr("href"),
        method: "get",
        error: function () {},
        success: function (response) {
          $el.removeClass("loading");
          $el.addClass("added");

          $(".siteHeader").find(".yith-woocompare-open").trigger("click");
        },
      });

      var compare = false;
      if ($(this).hasClass("added")) {
        compare = true;
      }

      if (compare === false) {
        var compare_counter = $compareCounter.html();
        compare_counter = parseInt(compare_counter, 10) + 1;

        setTimeout(function () {
          $(".siteHeader").find("#mini-compare-counter").html(compare_counter);
        }, 2000);
      } else {
        $el.removeClass("loading");
      }
    });

    $(document).on("click", ".compare-list .remove a", function (e) {
      e.preventDefault();
      var compare_counter = $(
        "#mini-compare-counter",
        window.parent.document
      ).html();
      compare_counter = parseInt(compare_counter, 10) - 1;
      if (compare_counter < 0) {
        compare_counter = 0;
      }

      $("#mini-compare-counter", window.parent.document).html(compare_counter);
    });

    $(document).on(
      "click",
      "#yith-woocompare .yith_woocompare_clear",
      function (e) {
        e.preventDefault();
        $("#mini-compare-counter", window.parent.document).html(0);
      }
    );

    $(document).on(
      "click",
      ".yith-woocompare-widget li a.remove",
      function (e) {
        e.preventDefault();
        var compare_counter = $(".siteHeader")
          .find("#mini-compare-counter")
          .html();
        compare_counter = parseInt(compare_counter, 10) - 1;
        if (compare_counter < 0) {
          compare_counter = 0;
        }

        setTimeout(function () {
          $(".siteHeader").find("#mini-compare-counter").html(compare_counter);
        }, 2000);
      }
    );

    $(document).on(
      "click",
      ".yith-woocompare-widget a.clear-all",
      function (e) {
        e.preventDefault();
        setTimeout(function () {
          $(".siteHeader").find("#mini-compare-counter").html("0");
        }, 2000);
      }
    );
  };

  cpvProductView();
  productQuantity();
  addCompare();
})(jQuery);
