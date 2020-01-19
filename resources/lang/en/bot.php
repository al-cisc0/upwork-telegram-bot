<?php

return [
    'invalid_id' => 'This id is invalid.',
    'canceled' => 'Command is canceled.',
    'admins_only' => 'This command is only for admins. And they will be notified about your activity.',
    'admin_warning' => 'Warning! User :name id: :id telegram id: :telegram_id tries to execute admin command!',
    'debug_message' => 'There is something wrong with new jobs delivery. Please check the logs.',
    'you_are_banned' => 'Sorry, but you are permanently banned by bot owner. Nothing of your messages or commands will be delivered anywhere.',
    'help' => [
        'description' => 'Hello! This bot will notify you with new jobs posted on Upwork. You can make some groups with it for sorting from different RSS or have everything in private chat with bot. Also you can filter jobs by country and keywords. Have fun and good luck!',
        'commands' => "/help - View commands list and service description\n/add\\_chat - Execute this command in chat for adding to your chat list\n/delete\\_chat - Delete chat from your chat list. All RSS feeds attached to deleted chat also will be deleted.\n/add\\_feed - Attach RSS feed search link\n/delete\\_feed - Delete RSS feed search link\n/list\\_feeds - List RSS feeds attached to current chat\n/set\\_period - Set update period for current chat\n/filter\\_countries - Exclude jobs from country list (comma separated)\n/filter\\_words - Exclude jobs contains selected keywords (comma separated)",
        'admin_commands' => "/help - View commands list and service description\n/add\\_chat - Execute this command in chat for adding to your chat list\n/delete\\_chat - Delete chat from your chat list. All RSS feeds attached to deleted chat also will be deleted.\n/add\\_feed - Attach RSS feed search link\n/delete\\_feed - Delete RSS feed search link\n/list\\_feeds - List RSS feeds attached to current chat\n/set\\_period - Set update period for current chat\n/filter\\_countries - Exclude jobs from country list (comma separated)\n/filter\\_words - Exclude jobs contains selected keywords (comma separated)\n/my\\_telegram\\_id - Get your telegram id\n/give\\_access - Give access to user (if bot usage is restricted)",
    ],
    'my_id' => [
        'response' => 'Your telegram id is :id'
    ],
    'request_access' => [
            'description' => 'Your account is not activated yet. You have to request access to use this bot. Please confirm request by command /request\\_access',
            'owner_not_set' => 'The bot setup was not completed. Owner did not set his telegram id yet. Please try later.',
            'user_request' => 'New user :name id: :id telegram id: :telegram_id requests the access to use this bot.',
            'request_sent' => 'Access request was sent to the owner of bot. Please wait for reaction. You will be notified with result.',
            'provide_id' => 'You are in access changing mode. Please send the id of user to give/deny him access. It was "id:" part of access request message or "cancel" to get back to regular mode.',
            'access_granted' => 'Congratulations! Now you have the access to execute any command and use this bot.',
            'access_denied' => 'Sorry. Access was denied by bot owner.',
            'access_changed' => 'Access column :column for user :name id: :id was changed to state :state'
        ],
    'ban' => [
        'provide_id' => 'You are in banned state changing mode. Please send the id of user to ban/unban him or "cancel" to get back to regular mode.',
        'banned' => 'Sorry, but you are permanently banned by bot owner. Nothing of your messages or commands will be delivered anywhere.',
        'unbanned' => 'Congratulations! You was unbanned.'
    ],
    'chat' => [
        'added' => 'Chat :title was successfully added to your chats list',
        'listing' => "Choose one of your chats:\n"
    ],
    'rss' => [
        'send_chat_id' => 'You are in RSS feed adding mode. Please choose one of your chats. All jobs from new RSS feed will be routed there. Send chat id or "cancel" to get back to regular mode.',
        'send_title' => 'Send title of your new feed. It will be used to hashtag messages.',
        'send_link' => 'Send RSS feed link copied from Upwork website.',
        'feed_added' => 'New RSS feed successfully added. Default update interval is 2 minutes but you can change it with /set\\_period command.',
        'country' => 'Country: :country',
        'view_job' => 'View job',
        'apply_job' => 'Appy job'
    ],
    'filter' => [
        'provide_countries' => "You are in countri filter setup mode. Send country names which you want to exclude comma separated (case sensitive). E.g. India,Pakistan. Or \"clear\" to delete existing filter. Or \"cancel\" to get back to regular mode.",
        'country_filter_set' => 'Filtering by country was changed. If you will want to change it, just execute this command again.'
    ]

];
