var gulp = require('gulp');
var gutil = require('gulp-util');
var uglify = require('gulp-uglify');
var sourcemaps = require('gulp-sourcemaps');
var concat = require('gulp-concat');
var imagemin = require('gulp-imagemin');
var del = require('del');
var babel = require('gulp-babel');
var sass = require('gulp-sass');
var browserSync = require('browser-sync').create();
var autoprefixer = require('gulp-autoprefixer');

var reload      = browserSync.reload;

var paths = {
    scripts: ['lib/js/**/*.js', 'lib/js/**/*.ts', '!lib/external/**/*.js'],
    styles: ['lib/css/**/*.css', 'lib/css/**/*.scss'],
    images: 'lib/img/**/*',
    html: 'lib/**/*.html',
    dest: './dist/'
};

gulp.task('clean', function() {
    return del(['./dist']);
});

// Copy and transpile all JavaScript
gulp.task('scripts', function() {
    return gulp.src(paths.scripts)
        .pipe(sourcemaps.init())
        .pipe(babel().on('error', function(e) {
            gutil.log(gutil.colors.bgRed(e.name, e.message));
            this.emit('end'); }))
        .pipe(concat('all.min.js'))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(paths.dest + 'js'));
});

// Minify and transpile copy all JavaScript
gulp.task('scripts-dist', function() {
    return gulp.src(paths.scripts)
        .pipe(babel({
            presets: ['es2015']
        }))
        .pipe(uglify())
        .pipe(concat('all.min.js'))
        .pipe(gulp.dest(paths.dest + 'js'));
});

// Copy and convert all CSS
gulp.task('styles', function () {
    return gulp.src(paths.styles)
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(autoprefixer({
            cascade: false
        }))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(paths.dest +'css'));
});

// Copy compress and convert all CSS
gulp.task('styles-dist', function () {
    return gulp.src(paths.styles)
        .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
        .pipe(gulp.dest(paths.dest +'css'));
});

// Copy static html
gulp.task('html', function() {
    gulp.src(paths.html)
        .pipe(gulp.dest(paths.dest));
});

// Copy all static images
gulp.task('images', function() {
    return gulp.src(paths.images)
        .pipe(imagemin({optimizationLevel: 5}))
        .pipe(gulp.dest(paths.dest + 'img'));
});

gulp.task('serve', function () {

    // Serve files from the root of this project
    browserSync.init({
        startPath:'./dist',
        server:{
            baseDir: './'
        }
    });

    gulp.watch(paths.dest + "**/*").on("change", reload);
});

// Rerun the task when a file changes
gulp.task('watch', function() {
    gulp.watch(paths.scripts, ['scripts']);
    gulp.watch(paths.styles, ['styles']);
    gulp.watch(paths.images, ['images']);
    gulp.watch(paths.html, ['html']);
});

// All tasks for distribution
gulp.task('distribute', function() {
    gulp.start('scripts-dist', 'styles-dist', 'images');
});

// The default task (called when you run `gulp` from cli)
gulp.task('default', ['watch', 'scripts', 'styles', 'images', 'html', 'serve']);
