<script type="text/javascript" src="/assets/js/vue.min.js"></script>
<script type="text/javascript" src="/assets/js/iview-2.14.2.js"></script>
<script type="text/javascript" src="/assets/js/util.js"></script>
<script type="text/javascript" src="/assets/js/request.js"></script>
<script type="text/javascript" src="/assets/js/methods.js"></script>
<?php
  if (isset($load_js_list) && !empty($load_js_list) && is_array($load_js_list)) {
    foreach ($load_js_list as $js) {
      echo '<script type="text/javascript" src="' . $js . '"></script>';
    }
  }
?>
</body>
</html>