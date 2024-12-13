<?php

    namespace app\Scheme;

    use App\Request;

    interface Modules
    {
        const standardTags = [
            // Document structure
            'html', 'head', 'body', 'title', 'meta', 'link', 'style', 'script', 'noscript',

            // Sections
            'div', 'span', 'header', 'footer', 'section', 'article', 'aside', 'main', 'nav',

            // Text content
            'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'br', 'hr', 'blockquote', 'q', 'cite',
            'strong', 'em', 'b', 'i', 'u', 'small', 'big', 'mark', 'time', 'progress',
            'meter', 'abbr', 'address', 'code', 'pre', 'kbd', 'var', 'samp', 'sub', 'sup',
            'wbr',

            // Lists
            'ul', 'ol', 'li', 'dl', 'dt', 'dd',

            // Tables
            'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'col', 'colgroup',

            // Forms
            'form', 'input', 'textarea', 'button', 'select', 'option', 'optgroup',
            'fieldset', 'legend', 'label', 'datalist', 'output',

            // Multimedia
            'img', 'video', 'audio', 'source', 'track', 'canvas', 'iframe', 'svg', 'path',
            'circle', 'rect', 'ellipse', 'line', 'polyline', 'polygon', 'text', 'use', 'g',
            'symbol', 'defs', 'linearGradient', 'radialGradient', 'stop', 'pattern', 'marker',

            // Interactive elements
            'a', 'area', 'map', 'button', 'details', 'summary', 'dialog', 'menu', 'menuitem',

            // Embedded content
            'object', 'embed', 'param',

            // Miscellaneous
            'script', 'noscript', 'style', 'template', 'slot', 'base', 'bdi', 'bdo', 'data',
            'keygen', 'picture', 'source', 'track', 'figcaption', 'figure', 'center',

            // Obsolete/deprecated (for reference only)
            'acronym', 'applet', 'basefont', 'big', 'blink', 'font', 'marquee', 'nobr', 'strike',
            'tt', 'u'
        ];

        public
        function render(array $params = []): string;
		
		public
		function loader(): string;

        public
        function ajax(Request $request);
    }