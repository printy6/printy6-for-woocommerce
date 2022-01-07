<div class="wrap printy6-admin" style="margin-top: 76px;">
  <div id="pt6wc-app-container"></div>
  <script type="text/javascript">
    if(!window.printy6_for_woocommerce_info) {
      window.printy6_for_woocommerce_info = {
        is_connect: false,
      }
    }
    window.printy6_for_woocommerce_info.printy6_domain = "<?php echo esc_attr(PT6WC_API_DOMAIN); ?>";
    window.printy6_for_woocommerce_info.shop_domain = "<?php echo urlencode( trailingslashit( get_home_url() ) ); ?>";
    window.printy6_for_woocommerce_info.assets_url = "<?php echo plugins_url(PT6WC_PLUGIN_ABSOLUTE . '/assets'); ?>";
    window.printy6_for_woocommerce_info.page_url = "<?php echo home_url($_SERVER['REQUEST_URI']); ?>";
  </script>
</div>
