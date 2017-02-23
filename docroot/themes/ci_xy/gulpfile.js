var gulp = require('gulp');
var sass = require('gulp-sass');
var watch = require('gulp-watch');
var shell = require('gulp-shell');
var notify = require('gulp-notify');
var browserSync = require('browser-sync');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');

// BrowserSync
gulp.task('browser-sync', ['sass', 'compress'], function() {
    browserSync.init({
        proxy: "d8cogov.dev.dd:8888"
    });
});

// Sass processing
gulp.task('sass', function () {
  return gulp.src('./scss/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass({
      noCache: true,
      outputStyle: "compressed",
      lineNumbers: false,
      loadPath: './css/*',
      sourceMap: true
    }))
    .pipe(sourcemaps.write('./maps'))
    .pipe(gulp.dest('./css'))
    .pipe(browserSync.reload({stream: true}))
    .pipe(notify({
      title: "SASS Compiled",
      message: "All SASS files have been recompiled to CSS.",
      onLast: true
    }));
});

// Compress / Concat JS
gulp.task('compress', function() {
  return gulp.src('js/**/*.js')
    .pipe(sourcemaps.init())
    .pipe(uglify())
    .pipe(concat('scripts.js'))
    .pipe(sourcemaps.write('./maps'))
    .pipe(gulp.dest('js'))
    .pipe(browserSync.reload({stream: true}))
    .pipe(notify({
      title: "JS Minified",
      message: "All JS files in the theme have been minified.",
      onLast: true
    }));
});

// Run drush to clear the theme registry
gulp.task('clearcache', function () {
  return gulp.src('', {read: false})
    .pipe(shell([
      'drush cr css-js',
    ]))
    .pipe(notify({
      title: "Caches cleared",
      message: "Drupal CSS/JS caches cleared.",
      onLast: true
    }));
});

// Default task to be run with `gulp`
gulp.task('default', ['sass', 'browser-sync', 'compress'], function() {
  gulp.watch("scss/**/*.scss", ['sass']);
  gulp.watch("js/**/*.js", ['compress']);
  gulp.watch("templates/**/*.twig").on('change', browserSync.reload);
});
