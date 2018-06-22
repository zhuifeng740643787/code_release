<?php include __DIR__ . DS . 'layout' . DS . 'header.php'; ?>

<div id="main">
  <div class="layout">
    <Layout>
      <Content :style="{padding: '0 50px'}">
        <Breadcrumb :style="{margin: '20px 0'}">
          <BreadcrumbItem>代码发布</BreadcrumbItem>
        </Breadcrumb>
        <Card>
          <div class="form-wrapper" style="min-height: 800px;">
            <div class="form-item">
              <label for="" class="form-label" required>服务器组</label>
              <div class="item-content">
                <i-select v-model="server_group_index" filterable style="width:300px" v-on:on-change="handleServerGroupChange" >
                  <i-option v-for="(item,key) in serverGroups" :value="key" :key="key">{{ item.name }}</i-option>
                </i-select>
                <div style="margin: 10px 0;">
                  <Checkbox v-for="(item, key) in servers" :true-value="item.id" :label="item.id" v-model="formItem.server_ids[key]">{{ item.name }}({{ item.host }})</Checkbox>
                </div>
              </div>
            </div>

            <div class="form-item">
              <label for="" class="form-label" required>项目组</label>
              <div class="item-content">
                <Card :bordered="true">
                  <div slot="title">
                    <i-select v-model="project_group_index" v-on:on-change="handleProjectGroupChange" filterable
                              style="width:300px">
                      <i-option v-for="(item, key) in projectGroups" :value="key" :key="key">{{ item.name }}</i-option>
                    </i-select>
                  </div>
                  <ul class="project-wrapper" v-show="formItem.projects.length > 0">
                    <li class="item-wrapper header">
                      <div class="item-main">
                        <div class="left-box">
                          <span class="item-header">项目名称</span>
                        </div>
                        <div class="right-box">
                          <span class="item-header">分支/标签</span>
                        </div>
                      </div>
                      <div class="item-action">
                        <span>操作</span>
                      </div>
                    </li>
                    <li class="item-wrapper" v-for="(project, project_key) in formItem.projects" :key="project_key">
                      <div class="item-main">
                        <div class="left-box">
                          <Tooltip placement="top-end">
                            <div slot="content">
                              <ul style="min-width: 300px;">
                                <li><span>git仓库：{{ project.repository }}</span></li>
                                <li>
                                  <span>保留文件：</span>
                                  <ul style="margin-left: 20px;">
                                    <li v-for="(file, file_key) in project.static_files" :key="file_key">{{ file }}</li>
                                  </ul>
                                </li>
                              </ul>
                            </div>
                            <span>{{ project.name }}</span>
                          </Tooltip>
                        </div>
                        <div class="right-box">
                          <i-select v-model="project.branch_tag" filterable style="width:300px;text-align: left;">
                            <option-group label="分支">
                              <i-option v-for="(branch, branch_key) in project.branches" :value="'branch-' + branch" :key="branch_key">{{ branch }}</i-option>
                            </option-group>
                            <option-group label="标签">
                              <i-option v-for="(tag, tag_key) in project.tags" :value="'tag-' + tag" :key="tag_key">{{ tag }}</i-option>
                            </option-group>
                          </i-select>
                        </div>
                      </div>
                      <div class="item-action">
                        <Tooltip content="删除" placement="top-end">
                          <i-button v-on:click="handleDeleteProjectButton($event, project_key)">
                            <Icon type="android-remove-circle" :size="20" color="#ff6600"></Icon>
                          </i-button>
                        </Tooltip>
                        <Tooltip content="文件替换" placement="top-end">
                          <i-button v-on:click="handleShowReplaceFileModal($event, project_key)">
                            <Icon type="arrow-swap" :size="20" color="#89d953"></Icon>
                          </i-button>
                        </Tooltip>
                      </div>
                    </li>
                  </ul>
                </Card>
              </div>
            </div>
            <div class="form-item">
              <label for="" class="form-label" required>项目存放路径</label>
              <div class="item-content">
                <i-input v-model="formItem.release_code_path" placeholder="项目发布路径" style="width: 300px"></i-input>
                <Alert style="display: inline-block;">项目路径，如路径为/acs/code/release,项目为mc3时，项目存放位置为/acs/code/release/mc3
                </Alert>
              </div>
            </div>
            <div class="form-item">
              <label for="" class="form-label" required>发版说明</label>
              <div class="item-content">
                <i-input v-model="formItem.remark" :autosize="{minRows: 3}" size="large" type="textarea"
                         placeholder="发版说明" style="max-width: 800px"></i-input>
              </div>
            </div>
            <div class="action-wrapper">
              <div class="buttons">
                <i-button type="primary" :disabled="!canSubmit" size="large" v-on:click="handleSubmit">提交</i-button>
                <i-button type="ghost" size="large" v-on:click="handleReset" style="margin-left: 8px">重置</i-button>
              </div>
            </div>
          </div>
          <Spin fix v-show="loading">
            <Icon type="load-c" size=18 class="spin-icon-load"></Icon>
            <p>{{ loading_message }}</p>
          </Spin>
        </Card>
      </Content>
      <Footer class="layout-footer-center"></Footer>
    </Layout>
  </div>

  <?php include __DIR__ . DS . '_replaceFileModal.php'; ?>
  <?php include __DIR__ . DS . '_editFileModal.php';?>
  <?php include __DIR__ . DS . '_processModal.php';?>

</div>

<?php include __DIR__ . DS . 'layout' . DS . 'footer.php'; ?>

