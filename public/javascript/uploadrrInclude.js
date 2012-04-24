var langArray = new Array('Durchsuchen', 'Um Multimedia-Dateien hochzuladen, klicken Sie bitte auf "Durchsuchen" ', ', oder schieben Sie die Multimedia-Dateien einfach in diese Box.', 'Erlaubte Dateitypen:', 'Upload', ' wird nicht akzeptiert.\r\nErlaubte Dateitypen: ', 'Löschen', 
	' gelöscht.', ' Datei', ' Dateien', 'Maximale Dateigröße: ', 'Keine Dateien ausgewählt.', 'Aktuelle Datei: ', 'Zusammenfassung: ', ' Datei(en) ', 'Ihre Datei(en) wurden erfolgreich hochgeladen.', 'Server meldete eine ungültigen JSON Antwort.', 'Die gesendete Datei und die Datei die empfangen wurde, stimmen nicht überein.', 'Upload fehlgeschlagen.', 'Uploade: ', ' hinzugefügt.', 'Aktuelle Datei: ');
    $(document).ready(function() {
        $('#uploadbox').Uploadrr({
		allowedExtensions:['.mp3','.wav','.ogg', '.flac'],
		simpleFile: false,
		maxFileSize: -1,
		progressGIF: './resources/images/pr.gif',
		target: 'upload.php',
	});
    });