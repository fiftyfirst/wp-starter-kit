# WordPress Starter Kit
The WordPress Starter Kit by 51ˢᵗ helps quickly set up a development environment for you.

## What is provided
* Build and deployment tasks using a Makefile
* Normalize.css, Modernizr, and jQuery 1.x via Bower.
* usemin.py automatically scans `header.php` and `footer.php` and minifies and concatenates CSS and JavaScript files to single files upon deployment.

## Usage
To get started, clone this repository and run `make init`:

    git clone https://github.com/fiftyfirst/wp-starter-kit.git 'wp-starter-kit'
    cd wp-starter-kit
    make init

Compile SASS files by running:

	make watch

Minify and concatenate CSS and JavaScript assets by running. Files are automaticly built when running a `deploy` or `staging` task:

	make build

Clean up minified files by running:

	make clean

Install additional dependencies using Bower.

	bower install --save slideshow
