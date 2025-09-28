#!/bin/bash

ls /docker-entrypoint-init.d/ > /dev/null
for file in /docker-entrypoint-init.d/*; do
    if [ -f "$file" ]; then
        # get the filename without the path
        filename=$(basename "$file")
        logfile="/docker-entrypoint-init.logs/$filename/$(date "+%Y%m%d-%H%M%S").log"
        echo "Running $filename"
        # make sure the file is executable
        chmod +x "$file"
        # create a directory for the logs if it doesn't exist
        mkdir -p /docker-entrypoint-init.logs/"$filename"
        # create a marker file to indicate that the script is running
        marker_file="/docker-entrypoint-init.workdir/running-$filename"
        touch "$marker_file"
        # run the script and redirect both stdout and stderr to a log file
        "$file" > "$logfile" 2>&1
        # capture the exit status of the script
        exit_code=$?
        # remove the marker file
        rm -f "$marker_file"
        # check the exit status of the script
        if [ $exit_code -eq 0 ]; then
            echo "$filename completed successfully."
        else
            echo "$filename failed with exit code $exit_code. Printing Logs:"
            # print the logs to the console
            cat "$logfile"
            # exit with the same error code to propagate the failure
            exit $exit_code
        fi
    fi
done

# run the main command
echo "Starting Caddy server..."
exec frankenphp run --config /etc/caddy/Caddyfile "$@"