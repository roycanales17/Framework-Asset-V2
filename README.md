# PHP FRAMEWORK

Install the bundle using Composer:
```
composer create-project roy404/reduced project-name
```

# DOCUMENTATION

The PHP framework is a custom-built solution aimed at enhancing code organization and promoting best practices in Object-Oriented Programming (OOPS) for PHP development. It offers a set of tools and features designed to streamline the development process and improve code maintainability.

## Key Components:

- `Artisan`: The framework includes a command-line interface (CLI) tool to automate repetitive tasks, such as code generation.
- `Routing`: The routing component provides a flexible and intuitive way to define routes for incoming HTTP requests, allowing developers to map URLs to specific controller actions.
- `Model`: The model component offers a convenient way to interact with the database using object-oriented principles, enabling developers to define and manipulate database records as PHP objects.
- `Middleware`: Middleware are filters that can be applied to incoming requests to perform tasks such as authentication, logging, or modifying request data before it reaches the controller.

## Purpose and Benefits:

The framework aims to improve code organization, maintainability, and scalability of PHP projects by enforcing best practices in OOPS and providing a set of tools to streamline development tasks. It encourages developers to write clean, modular, and reusable code, leading to more robust and maintainable applications.

## Future Development:

In future iterations, the framework could be enhanced with additional features such as authentication, authorization, caching, and validation, further solidifying its position as a comprehensive PHP development solution.

## Components:
A component is a reusable piece of code that encapsulates a specific part of the UI or logic, making your code more modular and easier to manage. In the example you've provided, we are using PHP output buffering to embed components within HTML.

**Example Usage of Fragment:**
```PHP
    return <<<HTML
        <>
            <Information>
                Information/Content here...
            </Information>
            <LoginForm title="Login" />
            <div>
                Simple html tags...
            </div>
        </>
    HTML;
```

1. `PHP HEREDOC Syntax (<<<HTML):` 
   - **Heredoc** allows you to define multi-line string literals without needing to escape quotes or special characters, making it ideal for embedding large chunks of HTML.
   - You use **<<<HTML** to start the heredoc, and it ends with the word **HTML** on a line by itself (or another identifier if you choose to name it differently).
2. `Fragment`
   - The fragment **<>** here looks like shorthand for a React Fragment, but it's being used as a placeholder to represent multiple components grouped together without an actual wrapper element (useful for returning multiple elements).
   - PHP itself doesn't natively support a <> fragment like in React, but you can think of it as a logical grouping of elements.

## Database Setup:

1. `Install Docker`: Ensure Docker and Docker Compose are installed on your system. You can download them from Docker's official website.
2. `Create a Docker Compose File`: Create a file named docker-compose.yml in your desired directory:

```yml
version: '3.8'

services:
  mysql:
    image: mysql:8  # You can use a specific version for better stability
    container_name: mysql_container
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: admin
      MYSQL_DATABASE: framework
      MYSQL_USER: admin
      MYSQL_PASSWORD: admin
    ports:
      - "3307:3306"
    volumes:
      - mysql_data:/var/lib/mysql

  phpmyadmin:
    image: phpmyadmin/phpmyadmin:latest
    container_name: phpmyadmin_container
    restart: unless-stopped
    environment:
      PMA_HOST: mysql
      PMA_PORT: 3306
      PMA_USER: admin
      PMA_PASSWORD: admin
    ports:
      - "8085:80"

volumes:
  mysql_data:
```

3. `Run Docker Compose` Navigate to the directory containing your docker-compose.yml file and run:
```bash
docker-compose up -d
```

4. `Access phpmyadmin`: enter the follow link below to the browser:
```link
http://localhost:8085
```

### Docker phpmyadmin error?

Try to disable `Docker x86_64/amd64 emulation` from docker setting.

