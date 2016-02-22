var gulp = require('gulp');
var exec = require('child_process').exec;
var riot = require('gulp-riot');

gulp.task('bower', function (cb)
{
    exec('./bower install', function (err, stdout, stderr)
    {
        console.log(stdout);
        console.log(stderr);
        cb(err);
    });
});

gulp.task('composer', function (cb)
{
    exec('composer install', function (err, stdout, stderr)
    {
        console.log(stdout);
        console.log(stderr);
        cb(err);
    });
});

gulp.task('riot', function (cb)
{
    gulp.src('./components/*.tag')
        .pipe(riot())
        .pipe(gulp.dest('./public/js/components'));
});

gulp.task('default', ['composer', 'bower', 'riot']);