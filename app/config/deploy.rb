set :application, "Music Tube"
set :domain,      "musictube.skyveostudio.com"
set :deploy_to,   "~/www/#{domain}"
set :app_path,    "app"

set :repository,  "git://github.com/marcoleong/musictube.git"
set :scm,         :git
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `subversion`, `mercurial`, `perforce`, or `none`

set :model_manager, "doctrine"
# Or: `propel`

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain                         # This may be the same as your `Web` server
role :db,         domain, :primary => true       # This is where Symfony2 migrations will run

set  :keep_releases,  3
set  :use_composer, true

set  :shared_children,     [app_path + "/logs", web_path + "/uploads", "vendor"]
set  :shared_files,      ["app/config/parameters.yml"]
set  :deploy_via, :remote_cache

set :dump_assetic_assets, true
set :php_bin, "/Applications/MAMP/bin/php/php5.3.6/bin/php"


# Be more verbose by uncommenting the following line
# logger.level = Logger::MAX_LEVEL