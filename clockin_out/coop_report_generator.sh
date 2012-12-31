#!/bin/sh

# The archive.php script will be run on each Sunday and 
# create a flag.on file if the next day is a new pay cycle.
# If that file is present on Monday when this script is run,
# then the if condition will be true and the following will execute.
if [ -f /var/www/coop/Report/flag.on ]; then

	# The new report is now the old report.
	cp /var/www/coop/Report/new_report.csv /var/www/coop/Report/old_report.csv;

	# Create a file with today's date
	/usr/local/bin/php /var/www/coop/Report/touch.php;

	# PHP CLI executes report-cli.php
	/usr/local/bin/php /var/www/coop/Report/report-cli.php;

	# Wait a little while to be safe // try without this
	# sleep 20 

	# Copy the new report into it's specified date field that has already
	# been created with touch.php
	cp /var/www/coop/Report/new_report.csv /var/www/coop/Report/*20*.csv;

	# Remove the leftover CSV file in the Report directory
	mv /var/www/coop/Report/*20*.csv /var/www/coop/Report/Archive/;

	# Remove the flag
	rm /var/www/coop/Report/flag.on;

fi
