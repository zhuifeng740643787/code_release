/**
 * Created by gongyidong on 2018/5/21.
 */

/**
 * 请求类
 * url string 请求地址
 * params object 请求参数
 * async boolean 是否异步
 * options object 选项 {dataType: 'json', headers: {'Content-Type': 'json'}}
 * @returns {{get: get, post: post}}
 * @constructor
 */
function Request() {
  var that = this
  return {
    get: function (options) {
      return that._request('GET', options)
    },
    post: function (options) {
      return that._request('POST', options)
    }
  }
}

// 请求
Request.prototype._request = function (method, options) {
  if (typeof options !== 'object' || typeof options.url === 'undefined' || options.url == '') {
      throw new Error('url不能为空')
  }
  var url = options.url,
    method = method ? method.toUpperCase() : 'GET',
    params = options.params ? options.params : {},
    async = options.async === false ? false : true,
    successCallback = options.success ? options.success : function(e, response) {
      console.log('success', response)
    },
    errorCallback = options.error ? options.error : function(e, error) {
      console.error('error', error)
    }
  // 将对象参数转为字符串
  Object.keys(params).forEach(function(key){
    if (typeof params[key] === 'object') {
      params[key] = JSON.stringify(params[key])
    } else if (typeof params[key] === 'boolean') {
      params[key] = params[key] ? 1 : 0
    }
  })
  var xhr = typeof XMLHttpRequest !== 'undefined' ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP')
  var sendBody = null
  if (method === 'GET') {
    // 组装url
    url = this.makeGetUrl(url, params)
  } else if (method === 'POST') {
    sendBody = this.makePostSendBody(params)
  }

  xhr.open(method, url, async)
  var dataType = options.dataType ? options.dataType : 'json'
  if (options && options.headers && typeof options.headers === 'object') {
    Object.keys(options.headers).forEach(function(key){
      xhr.setRequestHeader(key, options.headers[key])
    })
  }
  if (async) {
    xhr.timeout = options && options.timeout ? options.timeout : 60000 // 毫秒
    xhr.ontimeout = function (e) {
      console.log(e, 'ontimeout')
    }
  }


  xhr.onload = function (e) {
    if (xhr.status >= 400) {
      return errorCallback(e, '请求有误')
    }
    if (xhr.status != 200) {
      return
    }
    if (dataType === 'json') {
      return successCallback(e, JSON.parse(xhr.response))
    }
    return successCallback(e, xhr.response)
  }

  xhr.onerror = function(e) {
    console.error('errorr', e)
  }

  xhr.send(sendBody)
}

Request.prototype.makeGetUrl = function (url, params) {
  if (!params || typeof params !== 'object') {
    return url
  }
  var paramKeys = Object.keys(params)
  if (paramKeys.length === 0) {
    return url
  }
  url += '?'
  paramKeys.forEach(function (key) {
    url += key + '=' + params[key] + '&'
  })

  return url.substr(0, url.length - 1)
}

Request.prototype.makePostSendBody = function (params) {
  if (!params || typeof params !== 'object') {
    return
  }

  var formData = new FormData()
  Object.keys(params).forEach(function (key) {
    formData.append(key, params[key])
  })

  return formData
}



