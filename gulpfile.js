var gulp = require('gulp');

var autoprefixer = require('gulp-autoprefixer');
var concat = require('gulp-concat');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');
var cleancss = require('gulp-clean-css');

gulp.task('css', function() {
    return gulp.src('style.css')
        .pipe(autoprefixer({browsers: ['> 0.5%', 'last 2 versions', 'Firefox ESR', 'not ie <= 8']}))
        .pipe(cleancss({level: 1, rebase: false, inline: false}))
        .pipe(rename('style.min.css'))
        .pipe(gulp.dest(''));
});

gulp.task('vendor-css', function() {
    return gulp.src('css/src/*.css')
        .pipe(concat('vendor.css'))
        .pipe(autoprefixer({browsers: ['> 0.5%', 'last 2 versions', 'Firefox ESR', 'not ie <= 8']}))
        .pipe(gulp.dest('css'))
        .pipe(rename('vendor.min.css'))
        .pipe(cleancss({level: 1, rebase: false, inline: false}))
        .pipe(gulp.dest('css'));
});

gulp.task('js', function() {
    return gulp.src(['js/src/@(!(functions)*|functions+(?)).js', 'js/src/functions.js'])
        .pipe(concat('script.js'))
        .pipe(gulp.dest('js'))
        .pipe(rename('script.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest('js'));
});

gulp.task('watch', function() {
    gulp.watch('css/src/*.css', ['vendor-css']);
    gulp.watch('style.css', ['css']);
    gulp.watch('js/src/*.js', ['js']);
});

gulp.task('default', ['css', 'vendor-css', 'js', 'watch']);