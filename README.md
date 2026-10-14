# Laravel App Wrapper

This is the wrapper i use to run laravel based apps on my infrastructure.  
The App container is built automatically, so a lot of customization is possible.  
I am using Frankenphp as a base for the app image, but that can easily be changed to apache or anything else.  
The dev container is built on the same FrankenPHP image, so PHP and its extensions match production.  
  
The setup process is pretty simple:  

1. Put your laravel application into `app/src`
2. Update all names, urls, details etc. in the following files:
    - `app/Dockerfile`
    - `app/deployment-scripts/shared/*`
    - `docker-compose.yml`
    - `test.sh`
3. If needed, put SQL init files into `docker/init/mysql`
4. Update the Base-Path in `test.sh` to make sure the final containers are uploaded to your container registry.
5. Use `test.sh` to check if the project runs correctly
6. Deploy on your production hardware 👌 (I use Dokploy, but it should work with many more similar projects or just use `docker compose up -d`)

## Look and feel

The starter app uses [fltc](https://github.com/fsteltenkamp/laravel-twcss-components) components.
Components take a colour role (`color="primary"`, `danger`, … default `base`); the theme
decides which palette each role uses and the skin decides the look. Set an app's default
with `APP_SKIN` / `APP_THEME`, and limit what users can pick on *Settings → Appearance* in
`app/src/config/appearance.php`. Users' choices are stored on their account.

## Updating apps built from this template

See [UPGRADING.md](UPGRADING.md) for what changed in each template update and what an app
built from it has to do.
