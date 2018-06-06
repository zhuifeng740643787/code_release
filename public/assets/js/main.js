/**
 * Created by gongyidong on 2018/5/23.
 */
var that
new Vue({
  el: '#main',
  data: {
    formItem: {
      host: '',
      project_name: '',
      repository: '',
      branch: '',
      project_path: '/acs/code/release',
      replace_files: [
        {
          local_file: '',
          replace_file: ''
        },
      ], // 替换文件
      remark: '', // 发版说明
    },
    loading: false,// 加载中
    fileContentModal: {
      visible: false,
      index: 0,
      title: '文件详情',
      content: '',
    },
    selectHosts: {}, // 服务器列表
    selectRepositories: [], // 仓库列表
    selectBranches: [], // 分支列表
    allBranches: {}, // 所有仓库对应的分支
  },
  computed: {
    canSubmit: function() {
      var formItem = this.formItem
      return formItem.host && formItem.project_name && formItem.repository && formItem.branch && formItem.project_path
    }
  },
  methods: {
    // 仓库改变事件
    handleRepositoryChange: function(value) {
      that.formItem.project_name = value
      that.showBranches()
    },
    // 显示所有分支
    showBranches: function() {
      if (!that.formItem.project_name) {
        return that.$Message.error('请先选择git仓库')
      }

      // 判断分支是否已加载过
      if (that.allBranches[that.formItem.project_name] !== undefined) {
        that.selectBranches = that.allBranches[that.formItem.project_name];
        return;
      }
      that.loading = true
      that.request.get({
        url: '/git/branches',
        params: {
          project_name: that.formItem.project_name
        },
        success: function(e, response) {
          that.loading = false
          if (response.status === 'error') {
            return that.$Message.error(response.message)
          }

          that.selectBranches = response.result.rows
          that.allBranches[that.formItem.project_name] = response.result.rows
        }
      })
    },
    // 添加替换文件
    handleAddReplaceButton: function(e) {
      that.formItem.replace_files.push({
        local_file: '',
        replace_file: '',
      })
    },
    // 删除替换文件
    handleDeleteReplaceButton: function(e, index) {
      that.formItem.replace_files.splice(index, 1)
    },
    // 查看文件内容
    handleViewFileContent: function(index) {
      var file_name = that.formItem.replace_files[index].local_file
      if (!file_name) {
        return;
      }
      that.request.get({
        url: '/other/file/view',
        params: {
          file_name: file_name
        },
        success: function(e, response) {
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
        error: function(e, error) {
          console.error(error, '---')
        }
      })
    },
    // 修改文件
    handleChangeFileContent: function (index) {
      var file_name = that.formItem.replace_files[index].local_file
      if (!file_name) {
        return;
      }
      that.request.post({
        url: '/other/file/change',
        params: {
          file_name: file_name,
          content: that.fileContentModal.content
        },
        success: function(e, response) {
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
        error: function(e, error) {
          console.error(error, '---')
        }
      })
    },
    // 触发上传事件
    triggerUploadClick: function(event, index) {
      document.querySelectorAll('.upload-wrapper input[type=file]')[index].click()
    },
    // 处理上传文件改变事件
    handleUploadChange: function(event, index) {
      var files = event.target.files
      if (files.length == 0) {
        return false
      }
      this.upload(files[0], index)
    },
    upload: function(file, index) {
      var xhr = typeof XMLHttpRequest !== 'undefined' ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP')
      xhr.open('POST', '/other/upload', true)
      // 上传完成后的回调
      var formData = new FormData()
      formData.append('upload_file', file)
      // 上传结束
      xhr.onload = function () {
        if (xhr.status === 200) {
          var ret = JSON.parse(xhr.response);
          if (ret.status != 'success') {
            return that.$Message.error(ret.message)
          }
          that.formItem.replace_files[index].local_file = ret.result.file_name
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
      var formItem = {}
      Object.keys(that.formItem).forEach(function(key) {
        formItem[key] = that.formItem[key]
      })
      var replace_files = []
      formItem.replace_files.forEach(function(item) {
        replace_files.push({
          local_file: item.local_file,
          replace_file: item.replace_file
        })
      })
      formItem.replace_files = replace_files
      that.request.get({
        url: '/release',
        params: formItem,
        success: function(e, response) {
          that.loading = false
          if (response.status === 'error') {
            return that.$Message.error(response.message)
          }
          console.log(response)
          that.$Message.info('发布成功')
        },
      })
    },
    // 重置
    handleReset: function () {
      that.formItem = {
        host: '',
        project_name: '',
        repository: '',
        branch: '',
        project_path: '/ac/code/release',
        replace_files: [
          {
            local_file: '',
            replace_file: ''
          },
        ]
      }
    }
  },
  created: function() {
    that = this
  },
  mounted: function() {
    // 获取主机列表
    that.request = new Request();
    that.request.get({
      url: '/other/config',
      success: function(e, response) {
        if (response.status === 'error') {
          return that.$Message.error(response.message)
        }

        that.selectHosts = response.result.hosts
        that.selectRepositories = response.result.repositories
      },
      error: function(e, error) {
        console.error(error, '---')
      }
    })
  }
})