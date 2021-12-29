WP-Player V2.6.2
=========

WP-Player 一个迷你歌曲播放器，支持多歌曲播放，支持使用网易云音乐, 虾米音乐, QQ音乐, 百度音乐歌曲地址, 也支持自定义上传音乐。

## Demo参考
<http://webjyh.com/wp-player/>

## 源码说明
1. `./src/` 为源码目录
2. `./dist/` 为打包后输出目录
3. 请使用 `./dist/wp-player/` 文件夹下文件，此目录为插件目录可以打成 `zip` 包上传至 `wordpress` 后台

## 源码打包
``` bash
# install dependencies
npm install

# build for production with minification
npm run build
```

### 声明
本插件仅供个人学习研究使用，请勿作为各种商业用户，音乐版权归各音乐平台所有。

### Update Ver 2.6.2 (2021-12-30)
1. 修复 网易云音乐某些链接从http重写https的问题

### Update Ver 2.6.1 (2017-06-12)
1. 修复 PHP 语法报错问题

### Update Ver 2.6.0 (2017-06-07)
1. 新增 QQ音乐 百度音乐平台。
2. 新增 当前歌曲不能播放，跳转至下一曲。
3. 新增 随机播放歌曲功能。
4. 新增 $('[data-wp-player="wp-player"]').WPPlayer('reload'); 方法，让单页面用户 可重新初始化播放器。
5. 新增 $('[data-wp-player="wp-player"]').WPPlayer('destroy'); 方法，用于销毁播放器；
6. 新增 歌曲列表自动滚动到中间位置，在也不用为找不到当前歌曲烦恼。
7. 修复虾米，网易获取音乐失败问题。
8. 修复样式问题，改善收缩列表动画问题
9. 美化歌曲列表滚动条样式
10. 更新 SoundManager 版本至最新版本
11. 在此感谢 @metowolf 开发的 Meting 框架，为获取音乐提供了更简单的方式
12. 项目地址：https://github.com/metowolf/Meting
13. 此版本项目结够改动比较大，请删除插件重新安装最好

### Update Ver 2.5.1 (2015-02-06)
1. 紧急修复因2.5.0版本导致网易云音乐不能播放问题

### Update Ver 2.5.0 (2015-02-06)
1. 新增自定义上传歌曲支持多歌曲。
2. 歌曲按一行一个填写，歌曲内容列表按歌曲名显示。
3. 因考虑自定义上传将会有多条记录，所以才用一行一首歌曲来填写。
4. 修正歌曲只有两首时，上一首，下一首按钮无用情况。
5. 现阶段功能上已基本完成，年前将只会进行Bug修复，暂不会开发新功能。
6. 如还想用2.4.2 老版本 请戳：https://github.com/webjyh/WP-Player/releases

### Update Ver 2.4.2 (2015-01-29)
1. 修正因某些主题使用字体图标导致样式错位
2. 修正某些主题下歌单不显示的问题
3. 修正若干样式排版问题
4. 下个版本将会入手自定义上传多歌曲功能

### Update Ver 2.4.1 (2015-01-13)
1. 解决因关闭歌词功能导致的JS错误。
2. 修正移动端最小宽度问题。

### Update Ver 2.4.0  (2015-01-11)
1. 新增移动端播放适配（暂不支持移动端自动播放歌曲功能）。
2. 因移动端的兼容性问题，自动播放功能将无法使用。
3. 因歌词功能消耗较大，在移动端下将被禁用。
4. 在 iPhone 6、iPhone 5s、iPhone 5、iOS 8.x+系统 测试通过。
5. 在 Android 4.1+ 系统测试过能。
6. 因移动端机型混杂，各大浏览器厂商也更多，难免会有意料之外的Bug。
7. 修正移动端样式问题。
8. 如想用 v2.3.0 老版本，可去 https://github.com/webjyh/WP-Player/releases 下载

### Update Ver 2.3.1  (2015-01-08)
1. 紧急修复暂无歌词Bug
2. 修正图片缓存问题

### Update Ver 2.3.0  (2015-01-08)
1. 新增歌词预览功能（支持虾米和网易）
2. 因歌词预览功能比较耗费资源，可在单独添加播放器时，打开或关闭此功能。
3. 因获取歌词功能接口的一些特殊性，如在后台选择中未出现歌词功能，则表示当前站点暂不支持。
4. 因其调取接口获取歌词在处理歌词时间上难免会有些误差（请不要太在意这些细节）
5. 修正一些样式错误。
6. 如想用 v2.2.0 老版本，可去 https://github.com/webjyh/WP-Player/releases 下载

### Update Ver 2.2.0  (2015-01-01)
1. 新增网易云音乐网址的调用（支持单音乐页面、专辑页面、艺人页面、精选集[即歌单]页面）
2. 因网易的接口一些特殊性，如在后台选择网站类型中没有出现网易音乐，则您当前的站点暂不支持网易云音乐。
3. 其次要感谢牧风的音乐播放插件，作为参考实现了网易接口的思路。
4. 在次还要感谢此文作者 https://github.com/yanunon/NeteaseCloudMusic/wiki/网易云音乐API分析
5. 修正播放器请求容错处理方案。 
6. 去除短代码中 00:00 字样。
7. 修正播放器样式问题。
8. 如想用 v2.1.0 老版本，可去 https://github.com/webjyh/WP-Player/releases 下载

### Update Ver 2.1.0  (2014-12-27)
1. 因发现最近播放器在解析虾米地址时，出现不稳定的情况，采用最新发现的虾米接口。
2. 新接口将是直接调取虾米网接口，速度更快且稳定。
3. 为其保险起见，怕日后虾米封其接口，如获取失败，将移交给新浪云解析。
4. 去除播放器HTML中的 Loading 字样，为其前台显示更美观。
5. 修正播放器样式问题。
6. 修正某些情况下导致图片无法显示的情况。
7. 如想用 v2.0.1 老版本，可去 https://github.com/webjyh/WP-Player/releases 下载

### Update Ver 2.0.1  (2014-12-24)
1. 修正因接口调制后台而导致无法抓取虾米ID的问题
2. 后台无法抓取将采用 新浪云来解析
3. 修正播放器样式问题。

### Update Ver 2.0.0  (2014-12-23)
1. 播放器新版扁平皮肤
2. 支持虾米多歌曲播放
3. 支持单音乐页面、专辑页面、艺人页面、精选集页面
4. 重构虾米地址填写解析；现只需复制网址，便可自动解析虾米ID。
5. 将解析虾米地址移交至插件本身
6. 重构播放器JS，采用SoundManger2来管理
7. 支持IE6+，FireFox，Chrome;
8. 废除短代码 loop 参数
9. 关闭后台无用选项设置

### Update Ver 1.3.4  (2014-08-24)
1. 修正 弱干 Bug
2. 由百度云平台解析 换至 新浪云平台
3. 百度云平台解析至 2014-10月底废除，请各位小伙伴赶紧更新

### Update Ver 1.3.2  (2014-02-19)
1. 修正jQuery后台选项无用的Bug

### Update Ver 1.3.1  (2014-02-17)
1. 插件提交到官方 WordPress plugins 库
2. 因提交到官方所以删除一些无用文件
3. 由原来自带的jQuery库 修改调用WordPress自带jQuery库

### Update Ver 1.3.0  (2014-02-14)
1. 增加百度云API，使解析虾米音乐地址更稳定
2. 细节调整
3. 修复Bug
4. 代码重构
5. 初次使用请到插件设置页面，设置虾米解析API。

### Update Ver 1.2.0
1. 增加皮肤选择
2. 新增扁平化皮肤 具体效果查看本页面播放器
3. 增加插件设定按钮
4. 扁平化皮肤 只支持 Chrome 和 FireFox 游览器

### Update Ver 1.1.0
1. 因虾米API变动，解决获取歌曲地址问题！
2. 添加根据虾米ID自动获取歌曲信息
3. MeatBox 更加简洁化


##填写方法：
1. WP-Player 支持网易云音乐, 虾米音乐, QQ音乐, 百度音乐平台
2. 如在网易云音乐打开喜欢的歌曲页面，复制歌曲页面的网址如：http://music.163.com/#/song?id=191213
3. 并将复制的网址填写到后面的表单内。音乐类型将根据网址自动做出选择。
4. 点击获取音乐ID按钮，此时音乐ID出现在表单中。
5. 将短代码 [player autoplay="1" random="1"] 填入您的文章内容中。
6. 短代码中 autoplay 表示是否自动播放；参数"0"表示否；"1"表示是；
7. 短代码中 random 表示是否随机播放；参数"0"表示否；"1"表示是；
8. 支持播放歌单：单音乐页面、专辑页面、艺人页面、精选集页面。
9. PS：本插件需要您的服务器或主机支持 PHP 5.4+ and Curl, OpenSSL 模块已安装。
10. Tips：本插件仅供个人学习研究使用，请勿作为各种商业用户，音乐版权归各音乐平台所有
PS：一篇文章只能插入一个播放器，因为用了MetaBox获取参数，只能一篇文章使用一个。


## 使用方法：

WordPress 插件使用短代码来实现调用播放器
```html
[player]
[player autoplay="1" random="1"]
```
