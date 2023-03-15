const { src, dest, series, parallel, watch } = require('gulp');

const concat = require('gulp-concat');
const rename = require('gulp-rename');
const postcss = require('gulp-postcss');
const uglify = require('gulp-uglify');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');

const cssnanoConfig = {
    preset: ['default', {
        cssDeclarationSorter: false,
        colormin: false,
        mergeLonghand: false,
        reduceInitial: false,
        reduceTransforms: false
    }]
};


function css() {
    return src('style.css')
        .pipe(postcss([
            autoprefixer(),
            cssnano(cssnanoConfig)
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
            cssnano(cssnanoConfig)
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
