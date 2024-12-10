<?php

    namespace app\Helper;

    use App\Config;
    use App\Request;

    trait Skeleton
    {
        use Mapping;

        public
        function displayError(mixed $exception = null): string
        {
            if (!config('development')) {
                if (!file_exists($path = $this->createFullPath('error.php'))) {
                    die((new Request)->response(500)->json(['message' => "Internal Server Error!"]));
                }

                die(require $path);
            }

            ob_start();

            $exception = $exception ?? $this;
            $file = urlencode($exception->getFile());
            $line = $exception->getLine();

            // Define editor URL schemes
            $editorUrls = [
                'phpstorm' => "phpstorm://open?file=$file&line=$line",
                'vscode' => "vscode://file/$file:$line",
                'sublime' => "subl://open?file=$file&line=$line"
            ];

            $selectedUrl = $editorUrls[$exception->preferredIDE ?? 'phpstorm'] ?? $editorUrls['vscode'];

            echo '<div style="font-family: Arial, sans-serif; background-color: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;">';
            echo '<h2 style="color: #721c24;">Exception Details</h2>';
            echo '<hr style="border-color: #f5c6cb;">';
            echo '<table style="width: 100%; border-collapse: collapse;">';

            $sanitizedMessage = preg_replace(
                '/ in .*? on line \d+/',
                '',
                $exception->getMessage()
            );

            $table = [
                'Exception Type:' => strtoupper(get_class($this)),
                'Message:' => $exception->getMessage(),
                'File:' => $exception->getFile(),
                'Line:' => $exception->getLine(),
                'Error Code:' => $exception->getCode(),
                'Previous Exception:' => ($exception->getPrevious() ? $exception->getPrevious()->getMessage() : 'None'),
            ];

            foreach ($table as $label => $value) {
                echo '<tr>';
                echo '<td style="padding: 8px; border: 1px solid #f5c6cb; background-color: #f8d7da;"><strong>' . $label . '</strong></td>';
                echo '<td style="padding: 8px; border: 1px solid #f5c6cb; background-color: #f8d7da;">' . $value . '</td>';
                echo '</tr>';
            }

            echo '</table>';
            echo '<h3 style="color: #721c24;">Stack Trace</h3>';
            echo '<pre style="background-color: #f5f5f5; padding: 10px; border: 1px solid #ddd; border-radius: 5px; color: #333;">' . $exception->getTraceAsString() . '</pre>';

            echo '<h3 style="color: black;">Possible Related Issues</h3>';
            echo '<div style="background-color: #f5f5f5; padding: 10px; border: 1px solid #ddd; border-radius: 5px; color: #333;">';
            echo '<div id="error-loader_" style="width: 100%;text-align: center;">Loading...</div>';
            echo '<ul id="error-solution-links" style="padding-left: 15px;font-size: 13px;margin: 0"></ul>';
            echo '</div>';

            echo '<a href="' . $selectedUrl . '" style="display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #721c24; color: #fff; text-decoration: none; border-radius: 5px;">Navigate Error</a>';
            echo '</div>';

            ?>
            <script>
                async function searchStackOverflow(query) {
                    const loader = document.getElementById('error-loader_');
                    const listElement = document.getElementById('error-solution-links');
                    const apiUrl = 'https://api.stackexchange.com/2.3/search/advanced';
                    const params = new URLSearchParams({
                        order: 'desc',
                        sort: 'relevance',
                        q: query,
                        site: 'stackoverflow',
                        pagesize: 10
                    });

                    try {
                        const response = await fetch(`${apiUrl}?${params.toString()}`);

                        if (!response.ok) {
                            loader.style.display = 'No Entry Found.';
                            throw new Error('Failed to fetch Stack Overflow data.');
                        }

                        loader.style.display = 'none';
                        const data = await response.json();

                        if (!listElement) {
                            console.error('Element with id "error-solution-links" not found.');
                            return;
                        }

                        listElement.innerHTML = '';

                        if (data.items && data.items.length > 0) {
                            data.items.forEach(item => {
                                const listItem = document.createElement('li');
                                const link = document.createElement('a');
                                link.href = item.link;
                                link.textContent = item.title;
                                link.target = '_blank';
                                link.setAttribute('style', 'text-decoration: none; color: #333;');
                                listItem.style.marginTop = '5px';
                                listItem.appendChild(link);
                                listElement.appendChild(listItem);
                            });
                        } else {
                            listElement.innerHTML = '<li>No results found.</li>';
                        }
                    } catch (error) {
                        loader.style.display = 'none';
                        listElement.innerHTML = '<li>Something went wrong.</li>';
                        console.error('Error:', error.message);
                    }
                }

                searchStackOverflow(<?= json_encode($sanitizedMessage) ?>);
            </script>
            <?php
            return ob_get_clean();
        }
    }