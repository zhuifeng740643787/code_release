<Modal
  width="1000"
  :mask-closable="false"
  :closable="true"
  v-model="replaceFileModal.visible"
  title="文件替换"
>
  <Card :bordered="true">
    <div slot="title">
      <Tooltip content="添加" placement="top-end">
        <i-button v-on:click="handleAddReplaceButton">
          <Icon type="plus-round" :size="30" color="#ff6600"></Icon>
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
      <li class="item-wrapper" v-for="(item, key) in replaceFileModal.replace_files" :key="key">
        <div class="item-main">
          <div class="left-box">
            <div class="upload-wrapper">
              <input type="file" name="upload[]" style="display: none;"
                     v-on:change="handleUploadChange($event, key)">
              <div class="upload-click-box" v-on:click="triggerUploadClick($event, key)">
                <i-button type="ghost" icon="ios-cloud-upload-outline">文件上传</i-button>
              </div>
              <span v-on:click="handleViewFileContent(key)"
                    class="item-header">{{ item.local_file }}</span>
            </div>
          </div>
          <div class="center-box">
            <Icon type="arrow-right-a" :size="30" color="#ff0060"></Icon>
          </div>
          <div class="right-box">
            <i-input v-model="item.replace_file" placeholder="被替换的文件名或存放文件的目录，从项目目录的下一层写起">
                              <span slot="prepend">{{replaceFileModal.project_name}}/<span>
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
</Modal>