/**
 * @file registers the quote toolbar button and binds functionality to it.
 */

import { Plugin } from 'ckeditor5/src/core';
import { ButtonView } from 'ckeditor5/src/ui';
import icon from '../../../../icons/quote.svg';

export default class QuoteUI extends Plugin {
  init() {
    const editor = this.editor;

    // This will register the quote toolbar button.
    editor.ui.componentFactory.add('quote', (locale) => {
      const command = editor.commands.get('insertQuote');
      const buttonView = new ButtonView(locale);

      // Create the toolbar button.
      buttonView.set({
        label: editor.t('CKEditor Quote'),
        icon,
        tooltip: true,
      });

      // Bind the state of the button to the command.
      buttonView.bind('isOn', 'isEnabled').to(command, 'value', 'isEnabled');

      // Execute the command when the button is clicked (executed).
      this.listenTo(buttonView, 'execute', () =>
        editor.execute('insertQuote'),
      );

      return buttonView;
    });
  }
}
