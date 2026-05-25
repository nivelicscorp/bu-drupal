/**
 * @file defines InsertQuoteCommand, which is executed when the quote
 * toolbar button is pressed.
 */
// cSpell:ignore simpleboxediting

import { Command } from 'ckeditor5/src/core';

export default class InsertQuoteCommand extends Command {
  execute() {
    const { model } = this.editor;

    model.change((writer) => {
      // Insert <quote>*</quote> at the current selection position
      // in a way that will result in creating a valid model structure.
      model.insertContent(createQuote(writer));
    });
  }

  refresh() {
    const { model } = this.editor;
    const { selection } = model.document;

    // Determine if the cursor (selection) is in a position where adding a
    // quote is permitted. This is based on the schema of the model(s)
    // currently containing the cursor.
    const allowedIn = model.schema.findAllowedParent(
      selection.getFirstPosition(),
      'quote',
    );

    // If the cursor is not in a location where a quote can be added, return
    // null so the addition doesn't happen.
    this.isEnabled = allowedIn !== null;
  }
}

function createQuote(writer) {
  // Create instances of the three elements registered with the editor in
  // quote.js.
  const quote = writer.createElement('quote');
  const quoteAuthor = writer.createElement('quoteAuthor');
  const quoteQuote = writer.createElement('quoteQuote');

  // Append the title and description elements to the quote, which matches
  // the parent/child relationship as defined in their schemas.
  writer.append(quoteQuote, quote);
  writer.append(quoteAuthor, quote);

  // The quoteQuote text content will automatically be wrapped in a
  // `<p>`.
  writer.appendElement('paragraph', quoteQuote);

  // Return the element to be added to the editor.
  return quote;
}
