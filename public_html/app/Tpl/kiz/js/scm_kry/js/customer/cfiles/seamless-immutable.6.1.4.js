/* (c) 2016, Richard Feldman, github.com/rtfeldman/seamless-immutable/blob/master/LICENSE */!function(){"use strict";function a(a,b,c){Object.defineProperty(a,b,{enumerable:!1,configurable:!1,writable:!1,value:c})}function b(b,c){a(b,c,function(){throw new f("The "+c+" method cannot be invoked on an Immutable data structure.")})}function c(b){a(b,G,!0)}function d(a){return"object"==typeof a?null===a||Boolean(Object.getOwnPropertyDescriptor(a,G)):!0}function e(a){return!(null===a||"object"!=typeof a||Array.isArray(a)||a instanceof Date)}function f(a){var b=new Error(a);return b.__proto__=f,b}function g(a,d){c(a);for(var e in d)d.hasOwnProperty(e)&&b(a,d[e]);return Object.freeze(a),a}function h(b,c){var d=b[c];a(b,c,function(){return D(d.apply(b,arguments))})}function i(a,b){if(a in this&&this[a]===b)return this;var c=p.call(this);return c[a]=D(b),k(c)}function j(a,b){var c=a[0];if(1===a.length)return i.call(this,c,b);var d,e=a.slice(1),f=this[c];if("object"==typeof f&&null!==f&&"function"==typeof f.setIn)d=f.setIn(e,b);else{var g=e[0];d=""!==g&&isFinite(g)?j.call(M,e,b):u.call(N,e,b)}if(c in this&&f===d)return this;var h=p.call(this);return h[c]=d,k(h)}function k(b){for(var c in K)if(K.hasOwnProperty(c)){var d=K[c];h(b,d)}a(b,"flatMap",n),a(b,"asObject",q),a(b,"asMutable",p),a(b,"set",i),a(b,"setIn",j),a(b,"update",w),a(b,"updateIn",y);for(var e=0,f=b.length;f>e;e++)b[e]=D(b[e]);return g(b,J)}function l(b){return a(b,"asMutable",m),g(b,L)}function m(){return new Date(this.getTime())}function n(a){if(0===arguments.length)return this;var b,c=[],d=this.length;for(b=0;d>b;b++){var e=a(this[b],b,this);Array.isArray(e)?c.push.apply(c,e):c.push(e)}return k(c)}function o(a){if("undefined"==typeof a&&0===arguments.length)return this;if("function"!=typeof a){var b=Array.isArray(a)?a.slice():Array.prototype.slice.call(arguments);b.forEach(function(a,b,c){"number"==typeof a&&(c[b]=a.toString())}),a=function(a,c){return-1!==b.indexOf(c)}}var c=this.instantiateEmptyObject();for(var d in this)this.hasOwnProperty(d)&&a(this[d],d)===!1&&(c[d]=this[d]);return B(c,{instantiateEmptyObject:this.instantiateEmptyObject})}function p(a){var b,c,d=[];if(a&&a.deep)for(b=0,c=this.length;c>b;b++)d.push(r(this[b]));else for(b=0,c=this.length;c>b;b++)d.push(this[b]);return d}function q(a){"function"!=typeof a&&(a=function(a){return a});var b,c={},d=this.length;for(b=0;d>b;b++){var e=a(this[b],b,this),f=e[0],g=e[1];c[f]=g}return B(c)}function r(a){return!a||"object"!=typeof a||!Object.getOwnPropertyDescriptor(a,G)||a instanceof Date?a:a.asMutable({deep:!0})}function s(a,b){for(var c in a)Object.getOwnPropertyDescriptor(a,c)&&(b[c]=a[c]);return b}function t(a,b){function c(a,c,f){var g=D(c[f]),j=i&&i(a[f],g,b),k=a[f];if(void 0!==d||void 0!==j||!a.hasOwnProperty(f)||g!==k&&g===g){var l;l=j?j:h&&e(k)&&e(g)?k.merge(g,b):g,(k!==l&&l===l||!a.hasOwnProperty(f))&&(void 0===d&&(d=s(a,a.instantiateEmptyObject())),d[f]=l)}}if(0===arguments.length)return this;if(null===a||"object"!=typeof a)throw new TypeError("Immutable#merge can only be invoked with objects or arrays, not "+JSON.stringify(a));var d,f,g=Array.isArray(a),h=b&&b.deep,i=b&&b.merger;if(g)for(var j=0;j<a.length;j++){var k=a[j];for(f in k)k.hasOwnProperty(f)&&c(void 0!==d?d:this,k,f)}else for(f in a)Object.getOwnPropertyDescriptor(a,f)&&c(this,a,f);return void 0===d?this:B(d,{instantiateEmptyObject:this.instantiateEmptyObject})}function u(a,b){var c=a[0];if(1===a.length)return v.call(this,c,b);var d,e=a.slice(1),f=this[c];if(d=this.hasOwnProperty(c)&&"object"==typeof f&&null!==f&&"function"==typeof f.setIn?f.setIn(e,b):u.call(N,e,b),this.hasOwnProperty(c)&&f===d)return this;var g=s(this,this.instantiateEmptyObject());return g[c]=d,B(g,this)}function v(a,b){if(this.hasOwnProperty(a)&&this[a]===b)return this;var c=s(this,this.instantiateEmptyObject());return c[a]=D(b),B(c,this)}function w(a,b){var c=Array.prototype.slice.call(arguments,2),d=this[a];return this.set(a,b.apply(d,[d].concat(c)))}function x(a,b){for(var c=0,d=b.length;null!=a&&d>c;c++)a=a[b[c]];return c&&c==d?a:void 0}function y(a,b){var c=Array.prototype.slice.call(arguments,2),d=x(this,a);return this.setIn(a,b.apply(d,[d].concat(c)))}function z(a){var b,c=this.instantiateEmptyObject();if(a&&a.deep)for(b in this)this.hasOwnProperty(b)&&(c[b]=r(this[b]));else for(b in this)this.hasOwnProperty(b)&&(c[b]=this[b]);return c}function A(){return{}}function B(b,c){var d=c&&c.instantiateEmptyObject?c.instantiateEmptyObject:A;return a(b,"merge",t),a(b,"without",o),a(b,"asMutable",z),a(b,"instantiateEmptyObject",d),a(b,"set",v),a(b,"setIn",u),a(b,"update",w),a(b,"updateIn",y),g(b,H)}function C(a){return"object"==typeof a&&null!==a&&(a.$$typeof===F||a.$$typeof===E)}function D(a,b,c){if(d(a)||C(a))return a;if(Array.isArray(a))return k(a.slice());if(a instanceof Date)return l(new Date(a.getTime()));var e=b&&b.prototype,g=e&&e!==Object.prototype?function(){return Object.create(e)}:A,h=g();if(null==c&&(c=64),0>=c)throw new f("Attempt to construct Immutable from a deeply nested object was detected. Have you tried to wrap an object with circular references (e.g. React element)? See https://github.com/rtfeldman/seamless-immutable/wiki/Deeply-nested-object-was-detected for details.");c-=1;for(var i in a)Object.getOwnPropertyDescriptor(a,i)&&(h[i]=D(a[i],void 0,c));return B(h,{instantiateEmptyObject:g})}var E="function"==typeof Symbol&&Symbol["for"]&&Symbol["for"]("react.element"),F=60103,G="__immutable_invariants_hold",H=["setPrototypeOf"],I=["keys"],J=H.concat(["push","pop","sort","splice","shift","unshift","reverse"]),K=I.concat(["map","filter","slice","concat","reduce","reduceRight"]),L=H.concat(["setDate","setFullYear","setHours","setMilliseconds","setMinutes","setMonth","setSeconds","setTime","setUTCDate","setUTCFullYear","setUTCHours","setUTCMilliseconds","setUTCMinutes","setUTCMonth","setUTCSeconds","setYear"]);f.prototype=Error.prototype;var M=D([]),N=D({});D.from=D,D.isImmutable=d,D.ImmutableError=f,Object.freeze(D),"object"==typeof module?module.exports=D:"object"==typeof exports?exports.Immutable=D:"object"==typeof window?window.Immutable=D:"object"==typeof global&&(global.Immutable=D)}();