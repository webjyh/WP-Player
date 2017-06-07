/*!
 * @name     wp-player gulp
 * @desc     wp-player 打包压缩
 * @author   M.J
 * @date     2017-06-06
 * @URL      http://webjyh.com
 * @Github   https://github.com/webjyh/WP-Player
 * 
 */
var path = require('path'),
    fs = require('fs'),
    gulp = require('gulp'),
    uglify = require('gulp-uglify'),
    minifyCss = require('gulp-minify-css'),
    postcss = require('gulp-postcss'),
    autoprefixer = require('autoprefixer'),
	replace = require('gulp-replace'),
    pkg = require('./package.json');

var source = {base: 'src'};
var dist = __dirname + '/dist/wp-player';
var getDate = function() {
	var d = new Date();
	return d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
};

/* build style */
gulp.task('build:style', function (){
    gulp.src(['src/assets/css/**/*.css'], source)
        .pipe(postcss([autoprefixer(['iOS >= 7', 'Android >= 4.1'])]))
		.pipe(replace('<%=date%>', getDate()))
		.pipe(replace('<%=version%>', pkg.version))
        .pipe(minifyCss({
            keepBreaks: false,
            keepSpecialComments: '*'
        }))
        .pipe(gulp.dest(dist));
});

/* build script */
gulp.task('build:script', function () {
    gulp.src(['src/assets/js/**/*.js'], source)
        .pipe(uglify({
			output: {
				comments: /^!/,
			}
		}))
		.pipe(replace('<%=date%>', getDate()))
		.pipe(replace('<%=version%>', pkg.version))
        .pipe(gulp.dest(dist));
});

/* build assets */
gulp.task('build:assets', function (){
    gulp.src('src/assets/**/*.?(eot|svg|ttf|woff|png|jpg|gif|swf)', source)
        .pipe(gulp.dest(dist));
});

/* build file */
gulp.task('build:file', function (){
    gulp.src(['src/**/*.php', 'src/**/*.txt'], source)
		.pipe(replace('<%=date%>', getDate()))
		.pipe(replace('<%=version%>', pkg.version))
        .pipe(gulp.dest(dist));
});

/* release */
gulp.task('release', ['build:assets', 'build:style', 'build:script', 'build:file']);

/* task */
gulp.task('default', ['release']);
