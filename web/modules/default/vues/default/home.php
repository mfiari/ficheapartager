<div class="col-md-12">
	<div class="row">
		<?php if ($request->path !== false) : ?>
			<a href="?action=index">Accueil</a> > 
			<?php $current = $request->path; ?>
			<?php while (count($current->childrens) > 0) : ?>
				<a href="?action=index&parent=<?php echo $current->id; ?>"><?php echo utf8_encode($current->nom); ?></a> > 
				<?php $current = $current->childrens[0]; ?>
			<?php endwhile; ?>
			<span><?php echo utf8_encode($current->nom); ?></span>
		<?php endif; ?>
	</div>
	<div class="row">
		<?php foreach ($request->categories as $categorie) : ?>
			<div>
				<span><a href="?action=index&parent=<?php echo $categorie->id; ?>"><?php echo utf8_encode($categorie->nom); ?></a></span>
			</div>
			<hr />
		<?php endforeach; ?>
	</div>
</div>