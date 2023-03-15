const { src, dest, series, parallel, watch } = require('gulp');

const concat = require('gulp-concat');
const rename = require('gulp-rename');
const postcss = require('gulp-postcss');
const uglify = require('gulp-uglify');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');


function css() {
    return src('style.css')
        .pipe(postcss([
            autoprefixer(),
            cssnano()
        ]))
        .pipe(rename('style.min.css'))
        .pipe(dest('.'));
}

function vendorCss() {
    return src('css/src/*.css')
        .pipe(concat('vendor.css'))
        .pipe(postcss([
            autoprefixer()
        ]))
        .pipe(dest('css'))
        .pipe(rename('vendor.min.css'))
        .pipe(postcss([
            cssnano()
        ]))
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
