## About this Application

This is web application powered by Laravel 6.8 is backend of telegram bot. It makes possible to deliver Upwork jobs posting
RSS feed to users via telegram. Users are able to create multiple groups in telegram and attach multiple feeds to each so
feeds will be sorted in different groups.

Bot can work both open for everybody or restricted modes. If you will choose restricted mode users will be forced to 
request access to you and only after yours permission they will be able to use your bot.

You can contact me via email tsysov@gmail.com

## Install
You have to have configured Telegram bot. See Telegram documentation to figure out how to get it. Also you have
to set Telegram bot's webhook to your server with route like this:

`https://YOUR_DOMAIN_HERE/api/token/YOUR_BOT_TOKEN_HERE`

Also using HTTPS is mandatory. Telegram will not work without it.

1. Clone project
2. Copy .env.example to .env
3. In .env file fill in variables:

`TELEGRAM_BOT_TOKEN=` Your bot's access token

`TELEGRAM_BOT_OWNER_ID=` Your Telegram id. It will give ability to bot to recognize you as app owner. 
It's not your login like '@something'. It's unsigned integer value like '123456789'. You can get it from some
other Telegram bots or any other way.

`TELEGRAM_BOT_FREE_ACCESS=` 0 If you want to restrict public access to using your bot ( recommended because 
Upwork have request limit and growing of users of your bot count will be cause of feeds update delay ) and 1
if you want to force each user to request access individually.

4. Run commands:

`php composer update`

`php artisan key:generate`

`php artisan migrate`

5. Configure cron job for Laravel schedule (see Laravel docs [https://laravel.com/docs/master/scheduling])   

6. Configure workers for Laravel jobs stored in Database. One for 'default' queue (as many processes as you wish) and
one for 'feedUpdate' (only single process to prevent Upwork request limit exceeding). I recommend to use supervisor. 
See Laravel docs [https://laravel.com/docs/master/queues]

7. ???

8. PROFIT!

## Licence

This software is under MIT licence. (c) 2020 Alexandr Tsysov
