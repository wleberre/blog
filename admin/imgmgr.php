<?php
	include_once('includes/functions.php'); 
	$page['title']='Gestionnaire d´images';
	$page['windowTitle'] = 'Gestion des images';
	include_once ('includes/functions.php');
	include_once ('includes/imagesFunctions.php');
	secureAccess();
	$imagesRoot = getFromConfig('imgdirectory');
	if (!array_key_exists('imgmgr',$_SESSION)) $_SESSION['imgmgr']=array();
	//reglage du repertoire courant
	if ($_GET['chdir']) {
		$newDir = realpath($_SERVER['DOCUMENT_ROOT']).'/'.$_GET['chdir'];
		$absoluteImagesRoot = realpath($_SERVER['DOCUMENT_ROOT'].'/'.$imagesRoot);
		if (substr($newDir,0,strlen($absoluteImagesRoot)) == $absoluteImagesRoot) {
			$curDir = $_SESSION['imgmgr']['currentdir'] = $_GET['chdir'];
		}else {
			// il s´agit d´un chemin interdit
			$curDir = $_SESSION['imgmgr']['currentdir'] = $imagesRoot;
		}
	}elseif ($_SESSION['imgmgr']['currentdir']) {
		$curDir = $_SESSION['imgmgr']['currentdir'];
	}else {
		$curDir = $_SESSION['imgmgr']['currentdir'] = $imagesRoot;
	}
	$absoluteCurDir = realpath($_SERVER['DOCUMENT_ROOT'].'/'.$curDir).'/';

	if ($_GET['action']=='upload' && $_POST && $_FILES['imageFile']) {
		if (strpos($_FILES['imagefile']['type'],'image')!==0){
			$errMsg = '<div style="border:solid 2px red;background:pink;color:red;padding:1em;display:inline-block" class="droid">Le fichier n´est pas reconnu comme une image.</div>';
		}else {
			move_uploaded_file($_FILES['imagefile']['tmp_name'] , $absoluteCurDir.basename($_FILES['imagefile']['name']));
		}		
	}

	if ($_GET['action']=='createdir' && $crdir = basename ($_POST['directoryname'])) {
		if (mkdir($absoluteCurDir.$crdir)) {
			$curDir.=$crdir.'/';
			$absoluteCurDir = realpath($_SERVER['DOCUMENT_ROOT'].'/'.$curDir).'/';
		}else {
			$errMsg = '<div style="border:solid 2px red;background:pink;color:red;padding:1em;display:inline-block" class="droid">Impossible de créer le dossier '.$crdir.'</div>'; //cddir
		}
	}
printHeader($page,$errMsg);
?>
	<h2>Dossiers</h2>
	<p class="droid">Emplacement actuel :</p>
	<?php
		echo '/'.substr($curDir,strlen($imagesRoot),-1);
		//remonter d´un niveau ?
		if ($curDir != $imagesRoot) {
			print '<a href="?chdir='.dirname($curDir).'/" class="droid"> Remonter d´un niveau</a>';
		}
		// affichage des dossiers
		$dirs = glob($absoluteCurDir.'*', GLOB_ONLYDIR);
		if ($dirs) {
			print '<ul>';
			foreach ($dirs as $dir) {
				$dir=basename($dir);
				print '<li>';
				print '<a href="?chdir='.$curDir.$dir.'/" class="droid">'.$dir.'</a>';
				print '</li>';
			}
			print '</ul>';
		}
		?>
		<h2>Images</h2>
		<?php
			$imageFiles = array();
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			foreach (glob($absoluteCurDir.'*')as $filename) {
				if (strpos(finfo_file($finfo, $filename),'image')===0){
					$imageFiles[]=$filename;
				}
			}
			finfo_close($finfo);
			if ($imageFiles) {
				$width = getFromConfig('thumbWidth');
				$height = getFromConfig('thumbHeight');
				foreach ($imageFiles as $imageFile) {
					$url = substr(realpath($imageFile),strlen($_SERVER['DOCUMENT_ROOT']));
					print '<img src"'.getResized($imageFile,$width,$height).'" onclick="javascript:c=parent.document.getElementById(\'content\');c. value+=\'![texte]('.$url.')\';c.focus();">';
				}
			}
		?>
		<form enctype="multipart/form-data" method="POST" action="?action=upload">
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo getFromConfig('maxuploadedfilesize');?>">
			<fieldset><legend class="droid">Envoyer une image</legend>
				<label for="imagefile">Choisissez une image à télécharger :</label><br>
				<input type="file" name="imagefile" id="imagefile"><br>
				<input type="submit">
			</fieldset>
		</form>
		<form method="POST" action="?action=createdir">
			<fieldset><legend class="droid">Créer un dossier</legend>
				<label for="directoryname">Nom du dossier à créer :</label><br>
				<input name="directoryname" id="directoryname"><br>
				<input type="submit">
			</fieldset>
		</form>
	<?php
printFooter();
?>

