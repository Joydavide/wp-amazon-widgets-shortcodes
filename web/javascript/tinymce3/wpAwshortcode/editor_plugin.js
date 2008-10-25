(function(){tinymce.PluginManager.requireLangPack('wpAwshortcode');tinymce.create('tinymce.plugins.wpAwshortcodePlugin',{init:function(ed,url){var t=this;ed.addCommand('wpAwshortcodeSelector',function(ui,val){var popupArgs=[];popupArgs.push('tinymce='+escape(tinymce.baseURL));ed.windowManager.open({file:url+'/shortcode-'+val+'.html?'+popupArgs.join('&'),width:450+'px',height:470+'px',inline:1},{plugin_url:url,shortcode:val});});ed.onInit.add(function(){if(ed.settings.content_css!==false){dom=ed.windowManager.createInstance('tinymce.dom.DOMUtils',document);dom.loadCSS(url+'/css/content.css');ed.dom.loadCSS(url+'/css/content.css');}});ed.onNodeChange.add(function(ed){t._selectMenu(ed);});ed.onPostProcess.add(function(ed,o){o.content=o.content.replace(/<span class="awshortcode [^>]+>(.*\])<\/span>/g,function(text,shortcode){return shortcode.replace(/<\/?[^>]*>/g,'');});});ed.onSetContent.add(function(ed,o){var body=ed.getBody();if(/<span class="awshortcode amazon-[0-9a-z]+">/.test(body.innerHTML)){return;}body.innerHTML=body.innerHTML.replace(/(\[amazon-[a-z0-9]+[^\]]*\][^\[]+\[\/(amazon-[a-z0-9]+)\])/g,function(text,shortcode,widget_id){return'<span class="awshortcode '+widget_id+'">'+shortcode+'</span>';});});},createControl:function(n,cm){var t=this,menu=t._cache.menu,c,ed=tinyMCE.activeEditor,each=tinymce.each;if(n!='awshortcode-selector'){return null;}c=cm.createSplitButton(n,{cmd:'',scope:t,title:'wpAwshortcode.desc'});c.onRenderMenu.add(function(c,m){m.add({'class':'mceMenuItemTitle',title:'wpAwshortcode.desc'}).setDisabled(1);each(t.shortcodes,function(value,key){var o={icon:0},mi;o.onclick=function(){ed.execCommand('wpAwshortcodeSelector',true,key);};o.title=value;mi=m.add(o);menu[key]=mi;});t._selectMenu(ed);});return c;},shortcodes:{'amazon-carrousel':'wpAwshortcode.amazon_carrousel','amazon-myfavorites':'wpAwshortcode.amazon_myfavorites','amazon-product':'wpAwshortcode.amazon_product','amazon-productcloud':'wpAwshortcode.amazon_productcloud','amazon-slideshow':'wpAwshortcode.amazon_slideshow','amazon-wishlist':'wpAwshortcode.amazon_wishlist'},getInfo:function(){return{longname:'Amazon Widgets Shortcodes',author:'Oncle Tom',authorurl:'http://oncle-tom.net',infourl:'http://wordpress.org/extend/plugins/amazon-widgets-shortcodes/',version:'1.2'};},_cache:{menu:{}},_selectMenu:function(ed){var fe=ed.selection.getNode(),each=tinymce.each,menu=this._cache.menu;each(this.shortcodes,function(value,key){if(typeof menu[key]=='undefined'||!menu[key]){return;}menu[key].setSelected(ed.dom.hasClass(fe,key));});}});tinymce.PluginManager.add('wpAwshortcode',tinymce.plugins.wpAwshortcodePlugin);})();