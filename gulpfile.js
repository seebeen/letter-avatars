const argv         = require('yargs').argv;
const autoprefixer = require('gulp-autoprefixer');
const changed      = require('gulp-changed');
const concat       = require('gulp-concat');
const del          = require('del');
const log          = require('fancy-log');
const gulp         = require('gulp');
const color        = require('gulp-color');
const flatten      = require('gulp-flatten');
const gulpif       = require('gulp-if');
const newer        = require('gulp-newer');
const jshint       = require('gulp-jshint');
const imagemin     = require('gulp-imagemin');
const minifycss    = require('gulp-clean-css');
const plumber      = require('gulp-plumber');
const rename       = require('gulp-rename');

const sass         = require('gulp-sass'),
sassOptions = {
    errLogToConsole: true,
    outputStyle: 'expanded'
};
const uglify       = require('gulp-uglify');
const watch        = require('gulp-watch');
const lazypipe     = require('lazypipe');
const merge        = require('merge-stream');
const path         = require('path');
const runSequence  = require('run-sequence');
const webpack      = require('webpack-stream');

const pwd = __dirname;

var changeEvent = function(evt) {

    log.info(color('File '+ evt.event+': ','MAGENTA') + evt.path);

};

var logError =  function(error) {
    console.log(error.toString());
    this.emit('end');
};

var isAdmin = (argv.admin === undefined) ? false : true;

var absPaths = {
    src  : 'src/',
    dest : 'assets/',
};


if (isAdmin) {

    var basePaths = {
        src  : 'src/admin/',
        dest : 'assets/admin/',
    };

} else {

    var basePaths = {
        src  : 'src/frontend/',
        dest : 'assets/frontend/',
    };

}

var paths = {

    styles: {
        src: basePaths.src + 'styles/**/',
        dest: basePaths.dest + 'css/',      
    },
    vendorStyles: {
        src: basePaths.src + 'css',
        dest: basePaths.dest + 'css/',      
    },
    scripts: {
        src: basePaths.src + 'js/main',
        dest: basePaths.dest + 'js/',
    },
    vendorScripts: {
        src: basePaths.src + 'js/vendor',
        dest: basePaths.dest + 'js/vendor/',
    },
    tinymceScripts: {
        src: basePaths.src + 'js/tinymce',
        dest: basePaths.dest + 'js/tinymce',
    },  
    images: {
        src: absPaths.src + 'img/',
        dest: absPaths.dest + 'img/',
    },
    fonts: {
        src: absPaths.src + 'fonts/',
        dest: absPaths.dest + 'fonts/',
    },  
    
};

var vendorFiles = {
    styles: '',
    scripts: ''
};

var appFiles = {

    sassStyles: paths.styles.src + "main.scss",
    vendorStyles: paths.vendorStyles.src + "/*.css",
    scripts: paths.scripts.src + '/main.js',
    vendorScripts: paths.vendorScripts.src + '/*.js',
    tinymceScripts: paths.tinymceScripts.src + '/*.js',
    images: paths.images.src +'**',
    fonts: paths.fonts.src + '**'

};

gulp.task('bscss', function() {

    return gulp.src(['src/css/bootstrap.scss'])
        .pipe(sass())
        .on('error', logError)
        .pipe(gulp.dest('src/css'))
        .on('error', logError);

});

gulp.task('bsjs', function(){

    return gulp.src(['node_modules/bootstrap/dist/js/bootstrap.js','node_modules/popper.js/dist/umd/popper.js' ])
        .pipe(gulp.dest('src/js/vendor'));

});

gulp.task('vendorStyles', function(){
    return gulp.src(vendorFiles.styles.concat(appFiles.vendorStyles))
        .pipe(concat('vendor.min.css'))
        .pipe(autoprefixer())
        .pipe(minifycss())
        .pipe(gulp.dest(paths.vendorStyles.dest));
});

gulp.task('vendorScripts', function(){
    return gulp.src(vendorFiles.scripts.concat(appFiles.vendorScripts))
        .pipe(concat('vendor.min.js'))
        .pipe(gulp.dest(paths.scripts.dest))
        .pipe(uglify())
        .pipe(gulp.dest(paths.scripts.dest));
});

gulp.task('styles', function() {
    return gulp.src(appFiles.sassStyles)
        .pipe(plumber())
        .pipe(sass.sync())
        .pipe(gulpif('*.scss', sass({
            outputStyle: 'nested', // libsass doesn't support expanded yet
            precision: 10,
            //includePaths: ['./'],
        })))
        .pipe(autoprefixer())
        .pipe(minifycss())
        .pipe(concat('main.min.css'))
        .pipe(gulp.dest(paths.styles.dest));
});

gulp.task('scripts', function() {

    return gulp.src(appFiles.scripts)
        .pipe(plumber())
        .pipe(webpack())
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'))
        .pipe(uglify())
        .pipe(rename({
            basename : 'main',
            suffix   : '.min'
        }))
        .pipe(gulp.dest(paths.scripts.dest));

});

gulp.task('tinymceScripts', function() {

    return gulp.src(appFiles.tinymceScripts)
        .pipe(uglify())
        
        .pipe(gulp.dest(paths.tinymceScripts.dest));

});

gulp.task('images', function() {

    return gulp.src(appFiles.images)
        .pipe(newer(appFiles.images))
        .pipe(
            imagemin([
                imagemin.gifsicle({ interlaced: true }),
                imagemin.jpegtran({ progressive: true }),
                imagemin.optipng({ optimizationLevel: 5 }),
                imagemin.svgo({
                    plugins: [
                        {
                            removeViewBox: false,
                            collapseGroups: true
                        }
                    ]
                })
            ])
        )
        .pipe(gulp.dest(paths.images.dest));

});

gulp.task('fonts', function(){

    return gulp.src(appFiles.fonts).
        pipe(gulp.dest(paths.fonts.dest));

});

gulp.task('clean', function() {

    return del([
        basePaths.dest,
        paths.vendorStyles.src  + '/bootstrap.css',
        paths.vendorScripts.src + '/bootstrap.js',
        paths.vendorScripts.src + '/popper.js'
    ],
    {
        force: true
    }
    );

});

gulp.task('watch', function() {
    gulp.watch('src/css/bootstrap.scss', gulp.series('bscss'));
    gulp.watch(paths.scripts.src + "/**/**.js", gulp.series('scripts'));
    gulp.watch(paths.vendorScripts.src + '/*.js', gulp.series('vendorScripts'));
    gulp.watch(paths.vendorStyles.src + '/*.css', gulp.series('vendorStyles'));
    gulp.watch(paths.styles.src + "**/**.scss", gulp.series('styles'));
    gulp.watch(
        paths.images.src + "/**/*",
        {
            events: ['add','unlink'],
            usePolling: true
        },
        gulp.series('images')
    );
});


gulp.task('default',
    gulp.series(
        'clean',
        gulp.parallel(
            gulp.series('vendorStyles'),
            gulp.series('vendorScripts'),
            'styles', 'scripts', 'tinymceScripts', 'images', 'fonts'
        )
    )
);