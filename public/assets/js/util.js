/**
 * Created by gongyidong on 2018/6/19.
 */

function Utils() {
  // 获取数组中的某列
  this.getArrayColumn = function (arr, column_name) {
    if (typeof arr !== 'object') {
      return []
    }
    var ret = []
    Object.keys(arr).forEach(function (key) {
      if (typeof arr[key][column_name] !== 'undefined') {
        ret.push(arr[key][column_name])
      }
    })
    return ret
  }

  // 对象克隆
  this.cloneObject = function (obj) {
    return JSON.parse(JSON.stringify(obj))
  }
}

window.utils = new Utils()


