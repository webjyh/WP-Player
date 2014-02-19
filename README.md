WP-Player
=========

<p>WordPress 插件 WP-Player 是 一个迷你歌曲播放器，可以自己上传MP3，也可以使用虾米歌曲ID，理论支持格式mp3,m4a。</p>
<p><strong>Demo 预览：</strong>http://webjyh.com/wp-player/</p>

<blockquote>
	<p>更新说明：==== v1.3.2 ==== 2014-02-19</p>
	<ul>
		<li>1.修正jQuery后台选项无用的Bug</li>
	</ul>

	<p>更新说明：==== v1.3.1 ==== 2014-02-17</p>
	<ul>
		<li>1.插件提交到官方 WordPress plugins 库</li>
		<li>2.因提交到官方所以删除一些无用文件</li>
		<li>3.由原来自带的jQuery库 修改调用WordPress自带jQuery库</li>
	</ul>
	
	<p>更新说明：==== v1.3 ==== 2014-02-14</p>
	<ul>
		<li>1.增加百度云API，使解析虾米音乐地址更稳定</li>
		<li>2.细节调整</li>
		<li>3.修复Bug</li>
		<li>4.代码重构</li>
		<li>5.初次使用请到插件设置页面，设置虾米解析API。</li>
	</ul>

	<p>更新说明：==== v1.2 ====</p>
	<ul>
		<li>1.增加皮肤选择</li>
		<li>2.新增扁平化皮肤 具体效果查看本页面播放器</li>
		<li>3.增加插件设定按钮</li>
		<li>4.扁平化皮肤 只支持 Chrome 和 FireFox 游览器</li>
	</ul>

	<p>更新说明：==== v1.1 ====</p>
	<ul>
		<li>1.因虾米API变动，解决获取歌曲地址问题！</li>
		<li>2.添加根据虾米ID自动获取歌曲信息</li>
		<li>3.MeatBox 更加简洁化</li>
	</ul>
</blockquote>

<h4>使用方法：</h4>
<p>WordPress 插件使用短代码来实现调用播放器</p>
<pre>
[player]
[player loop="1" autoplay="1"]
</pre>

<h4>插件介绍：</h4>
<ol>
	<li>填写虾米歌曲ID</li>
	<li>自定义上传mp3文件，支持外链音乐文件</li>
	<li>自定义上传歌曲缩略图</li>
	<li>自定义歌曲名，歌手名</li>
	<li>自定义循环播放 loop=”1″ 和 自动播放 autoplay=”1″</li>
	<li>在同时填写虾米ID和歌曲地址时，优先采用虾米歌曲地址</li>
	<li>自动播放只有在具体文章页面和Page页面才有用</li>
</ol>

<blockquote><strong>PS：</strong>一篇文章只能插入一个播放器，因为用了MetaBox获取参数，只能一篇文章使用一个。多使用Over了，不要怪我。插件使用时，在插件设置页面，有个载入自带 jQuery 文件，此功能是有些主题以自带jQuery库，如果没有jQuery库的，可以勾选上这个勾，默认插件已勾选，如不需要请各位设置取消。</blockquote>
