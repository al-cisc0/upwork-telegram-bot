<?php

return [
    'invalid_id' => 'This id is invalid.',
    'canceled' => 'Command is canceled.',
    'more_desc' => 'See more description on Upwork.',
    'admins_only' => 'This command is only for admins. And they will be notified about your activity.',
    'admin_warning' => 'Warning! User :name id: :id telegram id: :telegram_id tries to execute admin command!',
    'debug_message' => 'There is something wrong with new jobs delivery. Please check the logs.',
    'you_are_banned' => 'Sorry, but you are permanently banned by bot owner. Nothing of your messages or commands will be delivered anywhere.',
    'help' => [
        'description' => 'Hello! This bot will notify you with new jobs posted on Upwork. You can make some groups with it for sorting from different RSS or have everything in private chat with bot. Also you can filter jobs by country and keywords. Have fun and good luck!',
        'commands' => "/help - View commands list and service description\n/add\\_chat - Execute this command in chat for adding to your chat list\n/delete\\_chat - Delete chat from your chat list. All RSS feeds attached to deleted chat also will be deleted.\n/add\\_feed - Attach RSS feed search link\n/delete\\_feed - Delete RSS feed search link\n/list\\_feeds - List RSS feeds attached to current chat\n/set\\_period - Set update period for current chat\n/filter\\_countries - Exclude jobs from country list (comma separated)\n/filter\\_description - Exclude jobs contains selected keywords (comma separated)\n/filter\\_title - Exclude jobs contains selected keywords (comma separated)\n/silence\\_schedule - Set time period with silent notifications\n/disable\\_all - Disable all your feeds\n/enable\\_all - Enable all your feeds\n/enable\\_chat - Enable all your feeds connected to current chat\n/disable\\_chat - Disable all your feeds connected to current chat",
        'admin_commands' => "/help - View commands list and service description\n/add\\_chat - Execute this command in chat for adding to your chat list\n/delete\\_chat - Delete chat from your chat list. All RSS feeds attached to deleted chat also will be deleted.\n/add\\_feed - Attach RSS feed search link\n/delete\\_feed - Delete RSS feed search link\n/list\\_feeds - List RSS feeds attached to current chat\n/set\\_period - Set update period for current chat\n/filter\\_countries - Exclude jobs from country list (comma separated)\n/filter\\_description - Exclude jobs contains selected keywords (comma separated)\n/filter\\_title - Exclude jobs contains selected keywords (comma separated)\n/silence\\_schedule - Set time period with silent notifications\n/disable\\_all - Disable all your feeds\n/enable\\_all - Enable all your feeds\n/enable\\_chat - Enable all your feeds connected to current chat\n/disable\\_chat - Disable all your feeds connected to current chat\n/my\\_telegram\\_id - Get your telegram id\n/give\\_access - Give access to user (if bot usage is restricted)\n/deny\\_access - Deny access to user (if bot usage is restricted)\n/ban - Permanently ban user. Any messages will be ignored.\n/unban - Unban user",
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
        'listing' => "Choose one of your chats:\n",
        'not_in_your_list' => "This chat is not in your list. Please add it first.",
        'deleted' =>  "Current chat deleted from your list with all connected feeds"
    ],
    'rss' => [
        'send_chat_id' => 'You are in RSS feed adding mode. Please choose one of your chats. All jobs from new RSS feed will be routed there. Send chat id or "cancel" to get back to regular mode.',
        'send_feed_id' => 'You are in RSS feed deleting mode. Please choose one of your feeds. Send feed id or "cancel" to get back to regular mode.',
        'send_title' => 'Send title of your new feed. It will be used to hashtag messages.',
        'send_link' => 'Send RSS feed link copied from Upwork website.',
        'feed_added' => 'New RSS feed successfully added. Default update interval is 2 minutes but you can change it with /set\\_period command.',
        'country' => 'Country: :country',
        'view_job' => 'View job',
        'apply_job' => 'Appy job',
        'chat_feeds_disabled' => 'All feeds connected to this chat are disabled.',
        'chat_feeds_enabled' => 'All feeds connected to this chat are enabled.',
        'listing' => "Choose one of your feeds:\n",
        'invalid_feed_id' => 'Chosen feed id is invalid. Please send another one.',
        'feed_deleted' => 'Chosen feed deleted.',
        'send_period' => 'You are in feeds update interval editing mode mode. Send interval in minutes (min 2) or "cancel" to get back to regular mode.',
        'invalid_period' => 'This interval value is invalid.',
        'period_set' => 'New feeds update interval :interval minutes was set.'
    ],
    'filter' => [
        'provide_countries' => "You are in country filter setup mode. Send country names which you want to exclude comma separated (case sensitive). E.g. India,Pakistan. Or \"clear\" to delete existing filter. Or \"cancel\" to get back to regular mode.",
        'country_filter_set' => 'Filtering by country was changed. If you will want to change it, just execute this command again.',
        'provide_title_keywords' => 'You are in title filter setup mode. Send keywords to search in job title and exclude the job if met (comma separated). E.g "urgent,cheap price". Or "clear" to delete existing filter. Or "cancel" to get back to regular mode.',
        'title_keywords_set' => 'Filtering by keywords in title was changed. If you will want to change it, just execute this command again.',
        'provide_description_keywords' => 'You are in description filter setup mode. Send keywords to search in job description and exclude the job if met (comma separated). E.g "urgent,cheap price". Or "clear" to delete existing filter. Or "cancel" to get back to regular mode.',
        'description_keywords_set' => 'Filtering by keywords in description was changed. If you will want to change it, just execute this command again.'
    ],
    'sleep_mode' => [
        'desc' => 'You are in silence schedule setup mode. Send time period you want to have all notifications without sound (feature not supported by some smart bands and watches). Please enter time in "23:00-08:00" format (EUROPE\MOSCOW TIMEZONE!). To disable/enable silence schedule send "toggle" and "cancel" to to get back to regular mode.',
        'wrong_format' =>  'This format is wrong. Please enter time in "23:00-08:00" format (EUROPE\MOSCOW TIMEZONE!).',
        'set' => 'Silence schedule is set and enabled. To change or disable please run this command one more time.',
        'toggle' => 'Silence schedule "enabled" state is changed to :state'
    ],
    'disable' => [
        'all_disabled' => 'All your feeds are disabled',
        'all_enabled' => 'All your feeds are enabled'
    ]

];
