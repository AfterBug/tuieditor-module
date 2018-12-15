var editor = new Editor({
    el: document.querySelector('#editor'),
    initialEditType: 'wysiwyg',
    initialValue: $('#editor').prev().val(),
    previewStyle: 'vertical',
    height: AfterBug.options.editor.height,
    hideModeSwitch: true,
    events: {
        change: function () {
            $('#editor').prev().val(editor.getMarkdown());
        }
    },
    toolbarItems: AfterBug.options.editor.toolbar,
});