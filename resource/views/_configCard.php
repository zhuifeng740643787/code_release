<Card v-show="menu.active=='config'">
  <div class="form-wrapper">
    <tabs size="large">
      <tab-pane label="服务器列表">
        <div class="table-box">
          <i-table border :columns="serverTable.columns" :data="serverTable.data"></i-table>
        </div>
      </tab-pane>
      <tab-pane label="项目列表">
        <div class="table-box">
          <i-table border :columns="projectTable.columns" :data="projectTable.data"></i-table>
        </div>
      </tab-pane>
    </tabs>
  </div>
</Card>