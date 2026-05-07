<?php
/*

recentcomments - 	an Axe plugin that shows an ad and recent comments from Disqus
				
					substitui %%#ADPLUSRECENTCOMM#%% pelos comentários + Adsense

© 2013 Augusto Campos http://augustocampos.net/ (1.06.2013)
Licensed under the Apache License, Version 2.0 (the "License"); 
you may not use this file except in compliance with the License. 
You may obtain a copy of the License at 
http://www.apache.org/licenses/LICENSE-2.0 

Unless required by applicable law or agreed to in writing, software 
distributed under the License is distributed on an "AS IS" BASIS, 
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
See the License for the specific language governing permissions and 
limitations under the License.

*/


/*
<li>%%RIGHTAD%%</li>
<br>&nbsp;
<li><h2>Coment<C3><A1>rios dos leitores</h2>
<div id="recentcomments" class="dsq-widget"><script type="text/javascript" src="http://brlinux.disqus.com/recent_comments_widget.js?num_items=3&hide_avatars=0&avatar_size=32&excerpt_length=200"> </script></div>                              
</li>
*/                                                    
                                                        

function recentcomments_index($trecho,$template,$p2='',$p3='',$p4='') {
	global $blogparms;
	if ($template=="footer") {
		$ad="<li>".$blogparms['RIGHTAD']."</li>";
		$comm='	<li><h2>Comentários dos leitores</h2>
				<div id="recentcomments" class="dsq-widget"><script type="text/javascript" src="http://brlinux.disqus.com/recent_comments_widget.js?num_items=3&hide_avatars=0&avatar_size=32&excerpt_length=200"> </script></div>                              
				</li>';
		$texto=$comm.$ad;
		$trecho=str_replace("%%#ADPLUSRECENTCOMM#%%",$texto,$trecho);
	}
	return $trecho;
}

function recentcomments_post($trecho,$template,$c_items=0,$p3='',$p4='') {
	global $blogparms;
	if ($template=="footer") {
		$ad="<li>".$blogparms['RIGHTAD']."</li>";
		$comm='	<li><h2>Comentários dos leitores</h2>
				<div id="recentcomments" class="dsq-widget"><script type="text/javascript" src="http://brlinux.disqus.com/recent_comments_widget.js?num_items=3&hide_avatars=0&avatar_size=32&excerpt_length=200"> </script></div>                              
				</li>';
		$texto=$ad.$comm;
		$trecho=str_replace("%%#ADPLUSRECENTCOMM#%%",$texto,$trecho);
	}
	return $trecho;
}




?>