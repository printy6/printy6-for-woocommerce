<div class="wrap printy6-admin" style="margin-top: 76px;">
  <div id="pt6wc-app-container"></div>
  <script type="text/javascript">
    //  is-scrolled
    window.addEventListener("scroll", function () {
      var scroll = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
      if(scroll > 60) {
        var dom = document.getElementsByClassName("pt6-class-header")[0];
        dom && dom.classList.add("is-scrolled");
      } else {
        var dom = document.getElementsByClassName("pt6-class-header")[0];
        dom && dom.classList.remove("is-scrolled");
      }
    });

    <?php
      global $wp;
      $woocommerce_printy6_settings = PT6_Base::get_settings();
    ?>
    
    if(!window.printy6_for_woocommerce_info) {
      window.printy6_for_woocommerce_info = {
        is_connect: true,
      };
    }
    window.printy6_for_woocommerce_info.user_id = "<?php echo esc_attr($woocommerce_printy6_settings['user_id']); ?>";
    window.printy6_for_woocommerce_info.store_id = "<?php echo esc_attr($woocommerce_printy6_settings['store_id']); ?>";
    window.printy6_for_woocommerce_info.access_token = "<?php echo esc_attr($woocommerce_printy6_settings['access_token']); ?>";
    window.printy6_for_woocommerce_info.printy6_domain = "<?php echo esc_attr(PT6WC_API_DOMAIN); ?>";
    window.printy6_for_woocommerce_info.shop_domain = "<?php echo urlencode( trailingslashit( get_home_url() ) ); ?>";
    window.printy6_for_woocommerce_info.assets_url = "<?php echo plugins_url(PT6WC_PLUGIN_ABSOLUTE . '/assets'); ?>";
    window.printy6_for_woocommerce_info.page_url = "<?php echo home_url($_SERVER['REQUEST_URI']); ?>";
  </script>
</div>
