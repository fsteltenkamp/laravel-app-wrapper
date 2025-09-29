# Laravel App Wrapper

This is the wrapper i use to run laravel based apps on my infrastructure.  
The App container is built automatically, so a lot of customization is possible.  
I am using Frankenphp as a base for the app image, but that can easily be changed to apache or anything else.  
  
The setup process is pretty simple:  

1. Put your laravel application into `app/src`
2. Update all the names etc. in the following files:
    - `app/Dockerfile`
    - `app/deployment-scripts/laravel-init.sh`
    - `docker-compose.yml`
3. If needed, put SQL init files into `docker/init/mysql`
4. Use `test.sh` to check if the project runs correctly
5. Deploy on your production hardware 👌 (I use Dokploy, but it should work with many more similar projects or just use `docker compose up -d`)