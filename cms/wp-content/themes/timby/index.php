<!DOCTYPE html>
<html lang="en" ng-app="timby">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{title}}</title>

    <!-- Bootstrap -->
    <!-- <link href="<?php echo get_template_directory_uri() ?>/css/bootstrap.css" rel="stylesheet"> -->
    <link href="<?php echo get_template_directory_uri() ?>/css/global.css" rel="stylesheet">

    <?php wp_head() ?>

    <style>
      /*button {
        height: 3em;
      }

      button.active {
        border-style: inset;
      }

      .scribe-html {
        width: 100%;
        font-family: monospace;
        border: 1px solid hsl(0, 0%, 80%);
        padding: 0.5em;
      }



      ui-rich-text-editor {
        display: block;
      }

      ui-rich-text-editor-toolbar {
        margin-bottom: 1em;
        display: block;
      }

      .ui-rich-text-editor__input-container {
        position: relative;
        border: 1px solid hsl(0, 0%, 80%);
      }

      .ui-rich-text-editor__placeholder {
        position: absolute;
        top: 0;
        left: 0;
        /* Allow the user to click through the placeholder */
        pointer-events: none;
        color: hsl(0, 0%, 45%);
      }

      .ui-rich-text-editor__placeholder,
      .ui-rich-text-editor__input {
        height: 370px;
        overflow: auto;
        padding: 0.5em;
      }*/
    </style>
  </head>
  <body data-template-url="<?php echo get_template_directory_uri() ?>" controller="MainController">

    <div ng-view></div>

    <?php wp_footer(); // this loads jQuery, Angular and our Angular app, see functions.php ?>

  </body>
</html>