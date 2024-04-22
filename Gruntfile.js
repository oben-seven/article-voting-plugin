module.exports = function(grunt) {
    require('time-grunt')(grunt);
    require('jit-grunt')(grunt);
  
    grunt.initConfig({
      pkg: grunt.file.readJSON('package.json'),
      sass: {
        dist: {
          files: {
            'assets/css/article-voting.css': 'assets/css/article-voting.scss'
          }
        }
      },
      cssmin: {
        target: {
          files: [{
            expand: true,
            cwd: 'assets/css',
            src: ['*.css', '!*.min.css'],
            dest: 'assets/css',
            ext: '.min.css'
          }]
        }
      },
      uglify: {
        dev: {
          files: [{
            expand: true,
            src: ['assets/js/*.js', '!assets/js/*.min.js'],
            dest: 'assets/js',
            cwd: '.',
            rename: function(dst, src) {
              return src.replace('.js', '.min.js');
            }
          }]
        }
      },
      watch: {
        scripts: {
          files: ['**/*.js', '**/*.scss', '**/*.css'],
          tasks: ['sass', 'cssmin', 'uglify'],
          options: {
            spawn: false
          }
        }
      }
    });
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.registerTask('default', ['sass', 'cssmin', 'uglify', 'watch']);
  };