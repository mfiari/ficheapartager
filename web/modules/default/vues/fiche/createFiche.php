<div class="row">
	<div class="col-md-12">
		<h2>Créer une fiche</h2>
		<div id="emailing">
			<form method="post" enctype="x-www-form-urlencoded" action="?controler=fiche&action=create">
				<fieldset>
					<div class="form-group">
						<label for="categorie">Catégorie<span class="required">*</span> : </label>
						<input class="form-control" type="text" name="categorie" value="<?php echo $request->fieldCategorie !== false ? $request->fieldCategorie : ''; ?>" required>
					</div>
					<div class="form-group">
						<label for="title">Titre<span class="required">*</span> : </label>
						<input class="form-control" type="text" name="title" value="<?php echo $request->fieldTitle !== false ? $request->fieldTitle : ''; ?>" required>
					</div>
					<div class="form-group">
						<label for="text">Texte<span class="required">*</span> : </label>
						<textarea class="form-control" name="text" rows="8" cols="45" required><?php echo $request->fieldText !== false ? $request->fieldText : ''; ?></textarea>
					</div>
					<button class="btn btn-primary" type="submit">Envoyer</button>
				</fieldset>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(function() {
		tinymce.init({
			selector: 'textarea',
			height: 500,
			plugins: [
				'advlist autolink charmap print preview anchor',
				'searchreplace visualblocks code fullscreen',
				'insertdatetime table contextmenu paste code textcolor'
			],
			menubar: "insert",
			toolbar: 'insertfile undo redo | styleselect | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
			content_css: '//www.tinymce.com/css/codepen.min.css',
			templates: [
				{title: 'Template 1', description: 'template', url: '../mails/avis.html'}
			],
			setup : function (editor) {
				editor.on('change', function () {
					editor.save();
				});
			}
		});
		
		$("#check_all").click(function () {
			if ($(this).is(':checked')) {
				$(".user_check").prop('checked', true);
			} else {
				$(".user_check").removeAttr('checked');
			}
		});
	});
</script>