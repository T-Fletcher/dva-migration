var AU=AU||{};!function(c){var e={};function r(e,t,n){"closing"===n?e.setAttribute("aria-expanded",!1):e.setAttribute("aria-expanded",!0)}function s(e,t,n,o){if("opening"===t||"open"===t)var a=n||"au-accordion--closed",i=o||"au-accordion--open";else a=o||"au-accordion--open",i=n||"au-accordion--closed";var l,c,r,s;s=a,(r=e).classList?r.classList.remove(s):r.className=r.className.replace(new RegExp("(^|\\b)"+s.split(" ").join("|")+"(\\b|$)","gi")," "),c=i,(l=e).classList?l.classList.add(c):l.className=l.className+" "+c}e.Toggle=function(e,t,o){try{window.event.cancelBubble=!0,event.stopPropagation()}catch(e){}void 0===e.length&&(e=[e]),"object"!=typeof o&&(o={});for(var n=0;n<e.length;n++){var a=e[n],i=a.getAttribute("aria-controls"),l=document.getElementById(i);if(null==l)throw new Error("AU.accordion.Toggle cannot find the target to be toggled from inside aria-controls.\nMake sure the first argument you give AU.accordion.Toggle is the DOM element (a button or a link) that has an aria-controls attribute that points to a div that you want to toggle.");l.style.display="block",function(n){c.animate.Toggle({element:l,property:"height",speed:t||250,prefunction:function(e,t){"opening"===t?(e.style.display="block","function"==typeof o.onOpen&&o.onOpen()):"function"==typeof o.onClose&&o.onClose(),r(n,0,t),s(n,t)},postfunction:function(e,t){"closed"===t?(e.style.display="",e.style.height="","function"==typeof o.afterClose&&o.afterClose()):(e.style.display="",e.style.height="","function"==typeof o.afterOpen&&o.afterOpen()),s(e,t)}})}(a)}return!1},e.Open=function(e,t){try{window.event.cancelBubble=!0,event.stopPropagation()}catch(e){}void 0===e.length&&(e=[e]);for(var n=0;n<e.length;n++){var o,a=e[n],i=a.getAttribute("aria-controls"),l=document.getElementById(i);o="undefined"!=typeof getComputedStyle?window.getComputedStyle(l).height:l.currentStyle.height,0===parseInt(o)&&(l.style.height="0px"),l.style.display="",s(l,"opening"),s(a,"opening"),r(a,0,"opening"),function(e,t,n){c.animate.Run({element:e,property:"height",endSize:"auto",speed:t||250,callback:function(){s(n,"opening")}})}(l,t,a)}},e.Close=function(e,t){try{window.event.cancelBubble=!0,event.stopPropagation()}catch(e){}void 0===e.length&&(e=[e]);for(var n=0;n<e.length;n++){var o=e[n],a=o.getAttribute("aria-controls"),i=document.getElementById(a);s(o,"closing"),r(o,0,"closing"),function(e,t){c.animate.Run({element:e,property:"height",endSize:0,speed:t||250,callback:function(){e.style.display="",s(e,"close")}})}(i,t)}},c.accordion=e}(AU),"undefined"!=typeof module&&(module.exports=AU);