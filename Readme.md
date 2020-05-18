# Chat

## Summary
A mobile first browser based chat application using PHP, Ajax and Javascript along with a host of supporting tools...HTML, CSS, Bootstrap 4, Jquery 3.5.1, MySQL and of course a websever like Apache on a server host like Linux. On the client you need a modern webbrowser like Safari or Chrome. Works on iOS, macOS and Windows. I'm sure it works on a standard Linux client because it works on a Raspberry Pi 4.

Now supports Markdown with [Parsedown](https://github.com/erusev/parsedown)

## PHP
Tested on [PHP](https://www.php.net) 7.3.8

## Database
Tested on [MySQL](https://dev.mysql.com/downloads/mysql/) 5.7.26

### Create the table structure

### Update the login information
Update Chat.php

private $host = 'localhost';  
private $user = 'user';  
private $password = "123";  
private $database = "chat";  

## Theory of Operation
The main goal is to perform most of the work on the server and to reduce the amount of network traffic requied. The tradeoff of course is that this is a pull-based application, not pushed-based. So this will increase the number of Ajax calls from the client to the server. So we tried to make these calls as small as possible. 

The default is every 1 second the client will send an Ajax request to the server to see if any new messages are avaiable. It uses a hash to only send back the complete message list when new messages are available.

Every 60 seconds the client will send a message asking for new status updates for all the users in the contact database.

Other reqeusts happen on demand, such as checking for typing status, etc.

## Main Components

Database

index.php

chat_action.php

Chat.php

chat.js


## ToDo

### Settings
- dark mode
- user selectable chat history length (reduce download size)


### Quick Message
- user managed list of single-button text
- a/c/d list of text shortcuts

### User Management
- add user
- delete user 
- reset password
- a/c/d user avatar

### Multimedia Support
- links - done added Parsedown 5/17/2020
- images
- video

### Notification
- Somehow notify user of new message (outside of page)

## Acknowledgements

Based on the chat tutorial by [PHPZag]( https://www.phpzag.com/build-live-chat-system-with-ajax-php-mysql/
)

