import { Plugin } from 'ckeditor5/src/core';
import { toWidget, toWidgetEditable } from 'ckeditor5/src/widget';
import { Widget } from 'ckeditor5/src/widget';
import InsertQuoteCommand from './insertquotecommand';

// cSpell:ignore insertquotecommand

/**
 * CKEditor 5 plugins do not work directly with the DOM. They are defined as
 * plugin-specific data models that are then converted to markup that
 * is inserted in the DOM.
 *
 * CKEditor 5 internally interacts with quote as this model:
 * <quote>
 *    <quoteAuthor></quoteAuthor>
 *    <quoteQuote></quoteQuote>
 * </quote>
 *
 * Which is converted for the browser/user as this markup
 * <blockquote>
 *   <p></p>
 *   <div class="author"></div>
 * </section>
 *
 * This file has the logic for defining the quote model, and for how it is
 * converted to standard DOM markup.
 */
export default class QuoteEditing extends Plugin {
  static get requires() {
    return [Widget];
  }

  init() {
    this._defineSchema();
    this._defineConverters();
    this.editor.commands.add(
      'insertQuote',
      new InsertQuoteCommand(this.editor),
    );
  }

  /*
   * This registers the structure that will be seen by CKEditor 5 as
   * <quote>
   *    <quoteAuthor></quoteAuthor>
   *    <quoteQuote></quoteQuote>
   * </quote>
   *
   * The logic in _defineConverters() will determine how this is converted to
   * markup.
   */
  _defineSchema() {
    // Schemas are registered via the central `editor` object.
    const schema = this.editor.model.schema;

    schema.register('quote', {
      // Behaves like a self-contained object (e.g. an image).
      isObject: true,
      // Allow in places where other blocks are allowed (e.g. directly in the root).
      allowWhere: '$block',
    });

    schema.register('quoteQuote', {
      // This creates a boundary for external actions such as clicking and
      // and keypress. For example, when the cursor is inside this box, the
      // keyboard shortcut for "select all" will be limited to the contents of
      // the box.
      isLimit: true,
      // This is only to be used within quote.
      allowIn: 'quote',
      // Allow content that is allowed in blocks (e.g. text with attributes).
      allowContentOf: '$root',
    });

    schema.register('quoteAuthor', {
      isLimit: true,
      allowIn: 'quote',
      allowContentOf: '$block',
    });

    schema.addChildCheck((context, childDefinition) => {
      // Disallow quote inside quoteQuote.
      if (
        context.endsWith('quoteQuote') &&
        childDefinition.name === 'quote'
      ) {
        return false;
      }
    });
  }

  /**
   * Converters determine how CKEditor 5 models are converted into markup and
   * vice-versa.
   */
  _defineConverters() {
    // Converters are registered via the central editor object.
    const { conversion } = this.editor;

    // Upcast Converters: determine how existing HTML is interpreted by the
    // editor. These trigger when an editor instance loads.
    //
    // If <blockquote> is present in the existing markup
    // processed by CKEditor, then CKEditor recognizes and loads it as a
    // <quote> model.
    conversion.for('upcast').elementToElement({
      model: 'quote',
      view: {
        name: 'blockquote',
      },
    });

    // If <h2 class="simple-box-title"> is present in the existing markup
    // processed by CKEditor, then CKEditor recognizes and loads it as a
    // <quoteAuthor> model, provided it is a child element of <quote>,
    // as required by the schema.
    conversion.for('upcast').elementToElement({
      model: 'quoteQuote',
      view: {
        name: 'p',
      },
    });

    // If <h2 class="simple-box-description"> is present in the existing markup
    // processed by CKEditor, then CKEditor recognizes and loads it as a
    // <quoteQuote> model, provided it is a child element of
    // <quote>, as required by the schema.
    conversion.for('upcast').elementToElement({
      model: 'quoteAuthor',
      view: {
        name: 'div',
        classes: 'author',
      },
    });

    conversion.for('upcast').elementToElement({
      model: 'quoteAuthor',
      view: {
        name: 'cite',
      },
    });

    // Data Downcast Converters: converts stored model data into HTML.
    // These trigger when content is saved.
    //
    // Instances of <quote> are saved as
    // <section class="simple-box">{{inner content}}</section>.
    conversion.for('dataDowncast').elementToElement({
      model: 'quote',
      view: {
        name: 'blockquote',
      },
    });

    // Instances of <quoteAuthor> are saved as
    // <h2 class="simple-box-title">{{inner content}}</h2>.
    conversion.for('dataDowncast').elementToElement({
      model: 'quoteQuote',
      view: {
        name: 'div',
        classes: 'quote',
      },
    });

    // Instances of <quoteQuote> are saved as
    // <div class="simple-box-description">{{inner content}}</div>.
    conversion.for('dataDowncast').elementToElement({
      model: 'quoteAuthor',
      view: {
        name: 'div',
        classes: 'author',
      },
    });

    // Editing Downcast Converters. These render the content to the user for
    // editing, i.e. this determines what gets seen in the editor. These trigger
    // after the Data Upcast Converters, and are re-triggered any time there
    // are changes to any of the models' properties.
    //
    // Convert the <quote> model into a container widget in the editor UI.
    conversion.for('editingDowncast').elementToElement({
      model: 'quote',
      view: (modelElement, { writer: viewWriter }) => {
        const blockquote = viewWriter.createContainerElement('blockquote', {});

        return toWidget(blockquote, viewWriter, { label: 'Quote widget' });
      },
    });

    // Convert the <quoteQuote> model into an editable <div> widget.
    conversion.for('editingDowncast').elementToElement({
      model: 'quoteQuote',
      view: (modelElement, { writer: viewWriter }) => {
        const div = viewWriter.createEditableElement('div', { classes: 'quote'});
        return toWidgetEditable(div, viewWriter);
      },
    });

    // Convert the <quoteAuthor> model into an editable <div> widget.
    conversion.for('editingDowncast').elementToElement({
      model: 'quoteAuthor',
      view: (modelElement, { writer: viewWriter }) => {
        const div = viewWriter.createEditableElement('div', {
          class: 'author',
        });
        return toWidgetEditable(div, viewWriter);
      },
    });
  }
}
