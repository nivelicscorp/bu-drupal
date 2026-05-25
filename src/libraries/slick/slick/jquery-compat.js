// Polyfill for jQuery 4.x compatibility ($.type removed)
if (typeof jQuery.type === 'undefined') {
  jQuery.type = function(obj) {
    if (obj == null) return obj + '';
    var class2type = {};
    'Boolean Number String Function Array Date RegExp Object Error Symbol'.split(' ').forEach(function(name) {
      class2type['[object ' + name + ']'] = name.toLowerCase();
    });
    return typeof obj === 'object' || typeof obj === 'function' ?
      class2type[Object.prototype.toString.call(obj)] || 'object' :
      typeof obj;
  };
}
if (typeof jQuery.isFunction === 'undefined') {
  jQuery.isFunction = function(obj) { return typeof obj === 'function'; };
}
if (typeof jQuery.isArray === 'undefined') {
  jQuery.isArray = Array.isArray;
}
