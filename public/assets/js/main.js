/**
 * Created by gongyidong on 2018/5/23.
 */
var that
new Vue({
  el: '#main',
  data: function () {
    return {
      formItem: {
        server_ids: [], // 要发布的服务器列表
        projects: [
          // {
          //   project_id: 0,
          //   branch: '',
          //   tag: '',
          //   replace_files: [
          //     {
          //       local_file: '',
          //       replace_file: '',
          //     },
          //   ],
          // }
        ],
        project_path: '/acs/code/release',
        remark: '', // 发版说明
      },
      server_group_index: -1, // 服务器组index
      project_group_index: -1, // 项目组index
      serverGroups: [], // 服务器组列表
      projectGroups: [], // 项目组列表
      servers: {}, //
      loading: false,// 加载中
      loading_message: '加载中...',
      replaceFileModal: {
        visible: false,
        project_index: -1,
        project_name: '',
        replace_files: [
          {
            local_file: '',
            replace_file: '',
          },
        ],
      },
      fileContentModal: {
        visible: false,
        index: 0,
        title: '文件详情',
        content: '',
      },
      // 发布进度modal
      progressModal: {
        visible: false, // 控制modal显示
        steps: [
          '拉取分支代码',
          '打包代码',
          '上传至服务器',
          '服务器端解压并部署代码包',
          '保留历史版本'
        ], // 发布的步骤
        servers: {
          'host1': {
            rate: 1, // 当前进度
            error: 'xx' // 是否有错，有错则停止
          },
          'host2': {
            host: '123',
            rate: 3, // 当前进度
            error: 'xx' // 是否有错，有错则停止
          },
        }
      },
    }
  },
  computed: {
    canSubmit: function () {
      var formItem = this.formItem
      return formItem.server_ids.length > 0 && formItem.project_path && formItem.projects.length > 0
    }
  },
  methods: {
    handleServerGroupChange: function () {
      that.servers = that.serverGroups[that.server_group_index]['servers']
      that.formItem.server_ids = window.utils.getArrayColumn(that.servers, 'id')
    },
    handleProjectGroupChange: function () {
      that.formItem.projects = window.utils.cloneObject(that.projectGroups[that.project_group_index]['projects'])
    },
    handleDeleteProjectButton: function (e, index) {
      that.formItem.projects.splice(index, 1)
    },
    handleShowReplaceFileModal: function (e, project_index) {
      var project = that.formItem.projects[project_index]
      that.replaceFileModal.visible = true
      that.replaceFileModal.project_index = project_index
      that.replaceFileModal.project_name = project.name
      that.replaceFileModal.replace_files = typeof project.replace_files !== 'undefined' ? window.utils.cloneObject(project.replace_files) : []
    },
    // 添加替换文件
    handleAddReplaceButton: function (e) {
      var item = {
        local_file: '',
        replace_file: '',
      }
      that.replaceFileModal.replace_files.push(item)
      var project = that.formItem.projects[that.replaceFileModal.project_index]
      if (typeof project.replace_files === 'undefined') {
        project.replace_files = []
      }
      project.replace_files.push(item)
    },
    // 删除替换文件
    handleDeleteReplaceButton: function (e, index) {
      that.replaceFileModal.replace_files.splice(index, 1)
      that.formItem.projects[that.replaceFileModal.project_index].replace_files.splice(index, 1)
    },
    // 查看文件内容
    handleViewFileContent: function (index) {
      var file_name = that.replaceFileModal.replace_files[index].local_file
      if (!file_name) {
        return;
      }
      that.request.get({
        url: '/other/file/view',
        params: {
          file_name: file_name,
        },
        success: function (e, response) {
          if (response.status === 'error') {
            return that.$Message.error(response.message)
          }
          // 显示内容
          that.fileContentModal = {
            visible: true,
            index: index,
            title: file_name,
            content: response.result.content,
          }
        },
        error: function (e, error) {
          console.error(error, '---')
        }
      })
    },
    // 修改文件
    handleChangeFileContent: function (index) {
      var file_name = that.replaceFileModal.replace_files[index].local_file
      if (!file_name) {
        return;
      }
      that.request.post({
        url: '/other/file/change',
        params: {
          file_name: file_name,
          content: that.fileContentModal.content
        },
        success: function (e, response) {
          if (response.status === 'error') {
            return that.$Message.error(response.message)
          }
          that.$Message.info('修改成功')
          // 显示内容
          that.fileContentModal = {
            visible: false,
            index: 0,
            title: '',
            content: '',
          }
        },
        error: function (e, error) {
          console.error(error, '---')
        }
      })
    },
    // 触发上传事件
    triggerUploadClick: function (event, index) {
      document.querySelectorAll('.upload-wrapper input[type=file]')[index].click()
    },
    // 处理上传文件改变事件
    handleUploadChange: function (event, index) {
      var files = event.target.files
      if (files.length == 0) {
        return false
      }
      this.upload(files[0], index)
    },
    upload: function (file, index) {
      var xhr = typeof XMLHttpRequest !== 'undefined' ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP')
      xhr.open('POST', '/other/upload', true)
      // 上传完成后的回调
      var formData = new FormData()
      formData.append('upload_file', file)
      formData.append('file_dir', that.replaceFileModal.project_name)
      // 上传结束
      xhr.onload = function () {
        if (xhr.status === 200) {
          var ret = JSON.parse(xhr.response);
          if (ret.status != 'success') {
            return that.$Message.error(ret.message)
          }
          that.replaceFileModal.replace_files[index].local_file = ret.result.file_name
          that.formItem.projects[that.replaceFileModal.project_index].replace_files[index].local_file = ret.result.file_name
        } else {
          that.$Message.error('上传失败')
        }
      }
      // 上传进度
      xhr.upload.onprogress = function (event) {
        if (event.lengthComputable) {
          var uploadPercent = (event.loaded / event.total * 100 | 0)
          console.log('process', uploadPercent);
        }
      }
      xhr.send(formData)
    },
    // 提交
    handleSubmit: function () {
      if (!this.canSubmit) {
        this.$Message.warning('请检查必填项');
        return;
      }
      that.loading = true
      that.loading_message = '提交中...'
      var formItem = {}
      Object.keys(that.formItem).forEach(function (key) {
        formItem[key] = that.formItem[key]
      })
      var replace_files = []
      formItem.replace_files.forEach(function (item) {
        replace_files.push({
          local_file: item.local_file,
          replace_file: item.replace_file
        })
      })
      // 当前只能上传一个
      formItem.servers = [formItem.host]
      formItem.replace_files = replace_files
      that.request.get({
        url: '/release',
        params: formItem,
        success: function (e, response) {
          that.loading = false
          if (response.status === 'error') {
            return that.$Message.error(response.message)
          }
          console.log(response)
          that.$Message.info('提交成功, 正在发布...')
        },
      })
    },
    // 重置
    handleReset: function () {
      that.formItem = {
        server_ids: [], // 要发布的服务器列表
        projects: [],
        project_path: '/acs/code/release',
        remark: '', // 发版说明
      }
    }
  },
  created: function () {
    that = this
  },
  mounted: function () {
    // 获取主机列表
    that.request = new Request();
    that.request.get({
      url: '/other/config',
      success: function (e, response) {
        if (response.status === 'error') {
          return that.$Message.error(response.message)
        }

        that.serverGroups = response.result.server_groups
        that.projectGroups = response.result.project_groups
      },
      error: function (e, error) {
        console.error(error, '---')
      }
    })
  }
})