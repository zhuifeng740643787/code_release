/**
 * Created by gongyidong on 2018/5/23.
 */
var that
new Vue({
  el: '#main',
  data: function () {
    return {
      // 菜单控制
      menu: {
        active: 'config', // 显示的menu
        list: {
          release: {
            name: '代码发布',
            icon: 'ios-navigate'
          },
          config: {
            name: '配置',
            icon: 'ios-keypad'
          },
        },
      },
      formItem: {
        server_ids: [], // 要发布的服务器列表
        projects: [],
        release_code_path: '/acs/code/release',
        remark: '', // 发版说明
      },
      server_group_index: -1, // 服务器组index
      project_group_index: -1, // 项目组index
      serverGroups: [], // 服务器组列表
      projectGroups: [], // 项目组列表
      servers: {}, //
      loading: false,// 加载中
      loading_message: '加载中...',
      // 文件替换模态框
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
      // 文件内容编辑模态框
      fileContentModal: {
        visible: false,
        index: 0,
        title: '文件详情',
        content: '',
      },
      // 发布进度模态框
      progressModal: {
        visible: false, // 控制modal显示
        group:{},
        sub: {},
      },
      // 服务器表格
      serverTable: {
        columns: [
          {
            title: '名称',
            key: 'name',
            align: 'center',
          },
          {
            title: 'IP',
            key: 'host',
            align: 'center',
          },
          {
            title: '登录用户',
            key: 'user',
            align: 'center',
          },
          {
            title: '端口',
            key: 'port',
            align: 'center',
          },
          {
            title: '状态',
            key: 'status_info',
            align: 'center',
          },
          {
            title: '创建时间',
            key: 'created_at',
            align: 'center',
          },
        ],
        data: [
        ],
      },
      // 项目表格
      projectTable: {
        columns: [
          {
            title: '名称',
            key: 'name',
            align: 'center',
          },
          {
            title: 'git仓库地址',
            key: 'repository',
            align: 'center',
          },
          {
            title: '状态',
            key: 'status_info',
            align: 'center',
          },
          {
            title: '创建时间',
            key: 'created_at',
            align: 'center',
          },
        ],
        data: [
        ],
      },
      // 定时任务
      loopFetchProgress: null,
    }
  },
  computed: {
    canSubmit: function () {
      var formItem = this.formItem
      // 基础信息检查
      if (!(formItem.server_ids.length > 0 && formItem.release_code_path && formItem.projects.length > 0)) {
        return false;
      }
      // 检查是否选择了分支或标签
      for (var i = 0; i < formItem.projects.length; i++) {
        if (typeof formItem.projects[i].branch_tag === 'undefined') {
          return false
        }
      }
      return true;
    }
  },
  methods: methods,
  created: function () {
    that = this
  },
  mounted: function () {
    // 获取主机列表
    that.request = new Request();
    // 获取配置信息
    that.fetchConfigInfo()
    // 获取表格信息
    that.fetchServerList()
    that.fetchProjectList()

    // 获取发布进度信息
    that.fetchProgressInfo()
  }
})