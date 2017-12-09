const gulp = require('gulp');

var
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    concat = require('gulp-concat'),
    cleanCSS = require('gulp-clean-css'),
    rename = require('gulp-rename'),
    watch = require('gulp-watch'),
    svgmin = require('gulp-svgmin'),
    svg = require('gulp-svg-inline-css');
//svgSprite = require("gulp-svg-sprites"),
//iconify = require("gulp-svgify"),
//cheerio = require('gulp-cheerio');


var sprites = {
        set1: './set1/*.svg',
        set2: './set3/*.svg',
        set1Bar: './set1/bar/*.svg',
        set2Bar: './set3/bar/*.svg'
    },
    des = './';

gulp.task('scss', function () {
    gulp.src("*.scss")
        .pipe(sass())
        .pipe(concat('style.css'))
        .pipe(autoprefixer({}))
        .pipe(cleanCSS())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest(des))
});

gulp.task('make-sprites', ['sprites', 'sprites-hover', 'sprites-toolbar', 'sprites-toolbar-hover']);

gulp.task('sprites', function () {
    gulp.src(sprites.set2)
        .pipe(svgmin())
        .pipe(svg({
            className: '.cms-tool-bar__icon.cms-tool-bar__icon--%s',
            style: {
                fill: '#b1b1b1'
            }
        }))
        .pipe(cleanCSS())
        .pipe(concat('sprites.min.css'))
        .pipe(gulp.dest(des))
});

gulp.task('sprites-hover', function () {
    gulp.src(sprites.set2)
        .pipe(svgmin())
        .pipe(svg({
            className: 'a:hover .cms-tool-bar__icon.cms-tool-bar__icon--%s',
            style: {
                fill: '#fcfcfc'
            }
        }))
        .pipe(cleanCSS())
        .pipe(concat('sprites-hover.min.css'))
        .pipe(gulp.dest(des))
});

gulp.task('sprites-toolbar', function () {
    gulp.src(sprites.set2Bar)
        .pipe(svgmin())
        .pipe(svg({
            className: 'a:hover .cms-tool-bar__icon.cms-tool-bar__icon--%s',
            style: {
                fill: '#fcfcfc'
            }
        }))
        .pipe(concat('sprites-toolbar.min.css'))
        .pipe(cleanCSS())
        .pipe(gulp.dest(des))
});

gulp.task('sprites-toolbar-hover', function () {
    gulp.src(sprites.set2Bar)
        .pipe(svgmin())
        .pipe(svg({
            className: '.cms-tool-bar__icon.cms-tool-bar__icon--%s',
            style: {
                fill: '#b1b1b1'
            }
        }))
        .pipe(concat('sprites-toolbar-hover.min.css'))
        .pipe(cleanCSS())
        .pipe(gulp.dest(des))
});


gulp.task('watch', function () {
    watch(["*.scss"], function (event, cb) {
        gulp.start('scss');
    });
});

/*
 gulp.task('prepare', function () {
 gulp.src('./123/*.svg')
 .pipe(cheerio({
 run: function ($) {
 $('[fill]').removeAttr('fill');
 $('[stroke]').removeAttr('stroke');
 $('[style]').removeAttr('style');
 }
 }))
 .pipe(gulp.dest("./321"))
 });

 gulp.task('default', function () {
 iconify({
 src: './321/*.svg',
 scssOutput: './scss',
 cssOutput: './css',
 styleTemplate: '_icon_gen.scss.mustache',
 defaultWidth: '300px',
 defaultHeight: '200px',
 svgoOptions: {
 enabled: true,
 options: {
 plugins: [
 {removeUnknownsAndDefaults: false},
 {mergePaths: false}
 ]
 }
 }
 });
 });
 */