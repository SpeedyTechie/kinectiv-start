const { src, dest, series, parallel, watch } = require('gulp');

const autoprefixer = require('gulp-autoprefixer');
const concat = require('gulp-concat');
const rename = require('gulp-rename');
const uglify = require('gulp-uglify');
const cleancss = require('gulp-clean-css');


function css() {
    return src('style.css')
        .pipe(autoprefixer())
        .pipe(cleancss({rebase: false, inline: false, compatibility: 'ie9'}))
        .pipe(rename('style.min.css'))
        .pipe(dest('.'));
}

function vendorCss() {
    return src('css/src/*.css')
        .pipe(concat('vendor.css'))
        .pipe(autoprefixer())
        .pipe(dest('css'))
        .pipe(rename('vendor.min.css'))
        .pipe(cleancss({rebase: false, inline: false, compatibility: 'ie9'}))
        .pipe(dest('css'));
}

function js() {
    return src(['js/src/@(!(functions)*|functions+(?)).js', 'js/src/functions.js'])
        .pipe(concat('script.js'))
        .pipe(dest('js'))
        .pipe(rename('script.min.js'))
        .pipe(uglify())
        .pipe(dest('js'));
}

function monitor(cb) {
    watch('style.css', css);
    watch('css/src/*.css', vendorCss);
    watch('js/src/*.js', js);
    
    cb();
}


exports.default = series(parallel(css, vendorCss, js), monitor);
