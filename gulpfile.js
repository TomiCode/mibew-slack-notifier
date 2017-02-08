var eventStream = require('event-stream'),
  gulp = require('gulp'),
  chmod = require('gulp-chmod'),
  zip = require('gulp-zip'),
  tar = require('gulp-tar'),
  gzip = require('gulp-gzip'),
  rename = require('gulp-rename');

gulp.task('prepare-release', function() {
  var version = require('./package.json').version;

  return eventStream.merge(
    getSources()
      .pipe(zip('mibdew-slack-notifier-' + version + '.zip')),
    getSources()
      .pipe(tar('mibdew-slack-notifier-' + version + '.tar'))
      .pipe(gzip())
  )
  .pipe(chmod(0644))
  .pipe(gulp.dest('release'));
});

gulp.task('default', ['prepare-release'], function() {});

var getSources = function() {
  return gulp.src([
    'Plugin.php',
    'README.md',
    'LICENSE'
  ],
    {base: './'}
  )
  .pipe(rename(function(path) {
    path.dirname = 'Mibew/Mibew/Plugin/SlackNotifier/' + path.dirname;
  }));
};
