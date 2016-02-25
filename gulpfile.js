var gulp = require('gulp');
var exec = require('child_process').exec;
var riot = require('gulp-riot');

var inputs = {
    "riot_tags": {
        "input": "./components/*.tag",
        "output": "./public/js/components"
    }
};

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
    gulp.src(inputs.riot_tags.input)
        .pipe(riot())
        .pipe(gulp.dest(inputs.riot_tags.output));
});

gulp.task('watch', function ()
{
    gulp.watch(inputs.riot_tags.input, ['riot']);
});

gulp.task('default', ['composer', 'bower', 'riot']);