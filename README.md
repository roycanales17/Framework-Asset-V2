# PHP FRAMEWORK

Install the bundle using Composer:
```
composer create-project roy404/framework product-name
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