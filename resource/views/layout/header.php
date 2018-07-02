<html>
<head>
  <meta charset="utf-8">
  <title>代码发布</title>
  <link rel="stylesheet" type="text/css" href="/assets/css/iview.css">
  <link rel="stylesheet" type="text/css" href="/assets/css/reset.css">
  <?php
  if (isset($load_css_list) && !empty($load_css_list) && is_array($load_css_list)) {
      foreach ($load_css_list as $css) {
          echo '<link rel="stylesheet" type="text/css" href="' . $css . '">';
      }
  }
  ?>
</head>
<body>
