var methods = {
  // 菜单改变事件
  handleChangeMenu: function (value) {
    that.menu.active = value
  },
  // 服务器组改变事件
  handleServerGroupChange: function () {
    that.servers = that.serverGroups[that.server_group_index]['servers']
    that.formItem.server_ids = window.utils.getArrayColumn(that.servers, 'id')
  },
  // 项目组改变事件
  handleProjectGroupChange: function () {
    that.formItem.projects = window.utils.cloneObject(that.projectGroups[that.project_group_index]['projects'])
  },
  // 删除项目事件
  handleDeleteProjectButton: function (e, index) {
    that.formItem.projects.splice(index, 1)
  },
  // 替换文件modal
  handleShowReplaceFileModal: function (e, project_index) {
    var project = that.formItem.projects[project_index]
    that.replaceFileModal = {
      visible: true,
      project_index: project_index,
      project_name: project.name,
      replace_files: typeof project.replace_files !== 'undefined' ? window.utils.cloneObject(project.replace_files) : []
    }
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
    var formItem = that.formItem
    var params = {
      server_ids: that._formatSubmitServerIds(),
      release_code_path: formItem.release_code_path,
      remark: formItem.remark,
      projects: that._formatSubmitProjects()
    }
    that.loading = true
    that.loading_message = '提交中...'
    that.request.get({
      url: '/release',
      params: params,
      success: function (e, response) {
        that.loading = false
        if (response.status === 'error') {
          return that.$Message.error(response.message)
        }
        console.log(response)
        that.$Message.info('提交成功, 正在发布...')
        // 将版本号写入localStorage
        window.localStorage.version_num = response.result.version_num
        // 定时获取发布状态
        that.fetchProgressInfo()
      },
    })
  },
  // 获取发布进度信息
  fetchProgressInfo: function () {
    if (that.loopFetchProgress != null || that.loopFetchProgress != undefined) {
      window.clearInterval(that.loopFetchProgress)
      that.loopFetchProgress = null
    }
    // 每3秒钟获取一次进度信息
    that.loopFetchProgress = window.setInterval(function () {
      var version_num = window.localStorage.version_num
      if (typeof version_num === 'undefined' || version_num == '') {
        window.clearInterval(that.loopFetchProgress)
        that.loopFetchProgress = null
        return
      }
      that.request.get({
        url: '/release/progress',
        params: {
          version_num: version_num
        },
        success: function (e, response) {
          if (response.status === 'error') {
            return that.$Message.error(response.message)
          }
          console.log(response)
          that.progressModal.visible = true;
          that.progressModal.group = response.result.group
          that.progressModal.sub = response.result.sub
          // 报错或者成功后，清除定时任务
          if (response.result.group.progress != 0) {
            window.localStorage.clear()
            window.clearInterval(that.loopFetchProgress)
            that.loopFetchProgress = null
            if (response.result.group.progress == -1) {
              that.$Message.error("任务出错了")
            } else {
              that.$Message.info("任务完成")
              that.handleReset()
            }
          }
        },
      })
    }, 3000);
  },
  // 格式化要提交的服务器ID
  _formatSubmitServerIds: function () {
    var arr = []
    var server_ids = that.formItem.server_ids
    server_ids.forEach(function (item) {
      if (item !== false) {
        arr.push(item)
      }
    })
    return arr
  },
  // 格式化要提交的项目信息
  _formatSubmitProjects: function () {
    var arr = []
    var projects = that.formItem.projects
    projects.forEach(function (item) {
      var replace_files = typeof item.replace_files !== 'undefined' ? item.replace_files : []
      var replace_files_arr = []
      for (var i = 0; i < replace_files.length; i++) {
        console.log(replace_files[i])
        if (replace_files[i].local_file != '') {
          replace_files_arr.push({
            local_file: replace_files[i].local_file,
            replace_file: replace_files[i].replace_file,
          })
        }
      }
      arr.push({
        id: item.id,
        branch_tag: item.branch_tag,
        replace_files: replace_files_arr
      })
    })
    return arr
  },
  // 重置
  handleReset: function () {
    that.formItem = {
      server_ids: [], // 要发布的服务器列表
      projects: [],
      release_code_path: '/acs/code/release',
      remark: '', // 发版说明
    }
  },
  // 获取配置信息
  fetchConfigInfo: function() {
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
  },
  // 获取服务器列表
  fetchServerList: function() {
    that.request.get({
      url: '/servers',
      success: function (e, response) {
        if (response.status === 'error') {
          return that.$Message.error(response.message)
        }
        that.serverTable.data = response.result.rows
      },
      error: function (e, error) {
        console.error(error, '---')
      }
    })
  },
  // 获取项目列表
  fetchProjectList: function() {
    that.request.get({
      url: '/projects',
      success: function (e, response) {
        if (response.status === 'error') {
          return that.$Message.error(response.message)
        }
        that.projectTable.data = response.result.rows
      },
      error: function (e, error) {
        console.error(error, '---')
      }
    })
  },
  // 获取服务器组
  fetchServerGroup: function() {
    that.request.get({
      url: '/server/group',
      success: function (e, response) {
        if (response.status === 'error') {
          return that.$Message.error(response.message)
        }
        that.serverGroupTable.data = response.result.rows
      },
      error: function (e, error) {
        console.error(error, '---')
      }
    })
  },
  // 获取项目组
  fetchProjectGroup: function() {
    that.request.get({
      url: '/project/group',
      success: function (e, response) {
        if (response.status === 'error') {
          return that.$Message.error(response.message)
        }
        that.projectGroupTable.data = response.result.rows
      },
      error: function (e, error) {
        console.error(error, '---')
      }
    })
  },
}
