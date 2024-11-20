<?php

    namespace config\Components;

    use app\Http\Requests\Request;
    use app\Scheme\Components;

    class DigitalCodes extends Components
    {
        public
        function render(array $params = []): string
        {
            $name = $params['name'];

            return <<<HTML
                <>
                    <label>Hi <b>$name</b></label>
                    <article>Welcome to our page!</article>
                    <form method="post" onsubmit="return submitForm(event)">
                        {$this->token()}
                        <input type="text" name="email" placeholder="Email Address" />
                        <input type="password" name="password" placeholder="Your Password" />
                        <input type="submit" value="Login" />
                    </form>
                </>
            HTML;
        }

        public
        function ajax(Request $request): bool
        {
            return false;
        }
    }