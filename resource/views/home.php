<html>
<head>
  <meta charset="utf-8">
  <title>代码发布</title>
  <link rel="stylesheet" type="text/css" href="/assets/css/reset.css">
  <link rel="stylesheet" type="text/css" href="/assets/css/main.css">
  <link rel="stylesheet" type="text/css" href="/assets/css/iview.css">
</head>
<body>
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
              <label for="" class="form-label" required>服务器</label>
              <div class="item-content">
                <i-select v-model="formItem.host" filterable style="width:600px">
                  <i-option v-for="(item,key) in selectHosts" :value="key" :key="key">{{ key }} - {{ item.host }}</i-option>
                </i-select>
              </div>
            </div>
            <div class="form-item">
              <label for="" class="form-label" required>git仓库地址</label>
              <div class="item-content">
                <i-select v-model="formItem.repository" v-on:on-change="handleRepositoryChange" filterable style="width:500px">
                  <i-option v-for="(item, key) in selectRepositories" :value="key" :key="key">{{ key }} - {{ item['address'] }}</i-option>
                </i-select>
              </div>
            </div>
            <div class="form-item">
              <label for="" class="form-label" required>项目名称</label>
              <div class="item-content">
                <i-input disabled v-model="formItem.project_name" placeholder="项目名称，英文" style="width: 300px"></i-input>
              </div>
            </div>
            <div class="form-item">
              <label for="" class="form-label" required>分支名称</label>
              <div class="item-content">
                <i-select v-model="formItem.branch" filterable style="width:300px">
                  <i-option v-for="(item, key) in selectBranches" :value="item" :key="key">{{ item }}</i-option>
                </i-select>
              </div>
            </div>
            <div class="form-item">
              <label for="" class="form-label" required>项目存放路径</label>
              <div class="item-content">
                <i-input v-model="formItem.project_path" placeholder="项目存放路径" style="width: 300px"></i-input>
                <Alert style="display: inline-block;">项目路径，如路径为/acs/code/release,项目为mc3时，项目存放位置为/acs/code/release/mc3</Alert>
              </div>
            </div>
            <div class="form-item">
              <label for="" class="form-label">文件替换</label>
              <div class="item-content">
                  <Card :bordered="true">
                    <div slot="title">
                      <Tooltip content="添加" placement="top-end">
                        <i-button v-on:click="handleAddReplaceButton">
                          <Icon type="plus-round" :size="30" color="#ff6600" ></Icon>
                        </i-button>
                      </Tooltip>
                    </div>
                    <ul class="replace-wrapper">
                      <li class="item-wrapper header">
                        <div class="item-main">
                          <div class="left-box">
                            <span class="item-header">本地文件</span>
                          </div>
                          <div class="center-box">
                            <Icon type="arrow-right-a" :size="30" color="#ff0060"></Icon>
                          </div>
                          <div class="right-box">
                            <span class="item-header">要替换的文件</span>
                          </div>
                        </div>
                        <div class="item-action">
                          <span>操作</span>
                        </div>
                      </li>
                      <li class="item-wrapper" v-for="(item, key) in formItem.replace_files" :key="key">
                        <div class="item-main">
                          <div class="left-box">
                            <div class="upload-wrapper">
                              <input type="file" name="upload[]" style="display: none;" v-on:change="handleUploadChange($event, key)">
                              <div class="upload-click-box" v-on:click="triggerUploadClick($event, key)">
                                <i-button type="ghost" icon="ios-cloud-upload-outline">文件上传</i-button>
                              </div>
                              <span v-on:click="handleViewFileContent(key)" class="item-header">{{ item.local_file }}</span>
                            </div>
                          </div>
                          <div class="center-box">
                            <Icon type="arrow-right-a" :size="30" color="#ff0060"></Icon>
                          </div>
                          <div class="right-box">
                            <i-input v-model="item.replace_file" placeholder="被替换的文件名或存放文件的目录，从项目目录的下一层写起" >
                              <span slot="prepend">{{formItem.project_name}}/<span>
                            </i-input>
                          </div>
                        </div>
                        <div class="item-action">
                          <Tooltip content="删除" placement="top-end">
                          <i-button v-on:click="handleDeleteReplaceButton($event, key)">
                            <Icon type="android-remove-circle" :size="20" color="#ff6600"></Icon>
                          </i-button>
                          </Tooltip>
                        </div>
                      </li>

                    </ul>
                  </Card>
              </div>
            </div>
            <div class="form-item">
              <label for="" class="form-label" required>备注</label>
              <div class="item-content">
                <i-input v-model="formItem.remark" :autosize="{minRows: 3}" size="large" type="textarea"  placeholder="发版说明" style="max-width: 800px"></i-input>

              </div>
            </div>
            <div class="action-wrapper">
              <div class="buttons">
                <i-button type="primary" size="large" v-on:click="handleSubmit">提交</i-button>
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

  <Modal
    v-model="fileContentModal.visible"
    :title="fileContentModal.title"
    @on-ok="handleChangeFileContent(fileContentModal.index)"
    :width="80">
    <i-input v-model="fileContentModal.content" :autosize="{minRows: 10}" size="large" type="textarea"></i-input>
  </Modal>

  <Modal
    width="1000"
    :mask-closable="false"
    :closable="true"
    v-model="progressModal.visible"
    title="发布进度"
    >
      <Card v-for="(item, host) in progressModal.hosts">
        <div slot="title" class="step-card-wrapper">
          <span class="card-title">{{ host }}</span>
          <div class="card-loading-box">
            <Spin v-show="true">
              <Icon type="load-c" size=10 class="spin-icon-load"></Icon>
            </Spin>
          </div>
        </div>
        <Steps :current="item.rate">
          <Step v-for="(content, index) in progressModal.steps" :title="index<item.rate?'已完成':(index==item.rate?'进行中':'待进行')" :content="content"></Step>
        </Steps>
      </Card>
      <p slot="footer"></p>
  </Modal>
</div>

<script type="text/javascript" src="/assets/js/vue.min.js"></script>
<script type="text/javascript" src="/assets/js/iview.min.js"></script>
<script type="text/javascript" src="/assets/js/request.js"></script>
<script type="text/javascript" src="/assets/js/main.js"></script>
</body>
</html>