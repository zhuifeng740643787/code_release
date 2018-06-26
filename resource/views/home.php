<?php include __DIR__ . DS . 'layout' . DS . 'header.php'; ?>

<div id="main">
  <div class="layout">
    <Layout>
      <Header>
        <i-menu mode="horizontal" theme="dark" ref="menuRef" :active-name="menu.active" v-on:on-select="handleChangeMenu">
          <div class="layout-nav">
            <menu-item v-for="(item, key) in menu.list" :name="key" :key="key">
              <Icon :type="item.icon"></Icon>
              {{ item.name }}
            </menu-item>
          </div>
        </i-menu>
      </Header>
      <Content :style="{padding: '0 50px'}">
        <Breadcrumb :style="{margin: '20px 0'}">
          <BreadcrumbItem>{{menu.list[menu.active].name}}</BreadcrumbItem>
        </Breadcrumb>
        <?php include __DIR__ . DS . '_releaseCard.php'; ?>
        <?php include __DIR__ . DS . '_configCard.php'; ?>
      </Content>
      <Footer class="layout-footer-center"></Footer>
    </Layout>
  </div>

  <?php include __DIR__ . DS . '_replaceFileModal.php'; ?>
  <?php include __DIR__ . DS . '_editFileModal.php';?>
  <?php include __DIR__ . DS . '_processModal.php';?>

</div>

<?php include __DIR__ . DS . 'layout' . DS . 'footer.php'; ?>

