from pathlib import Path
import re
from PIL import Image

root = Path(r'c:\xampp\htdocs')
images_dir = root / 'Imagenes'
exts = {'.jpg', '.jpeg', '.png', '.gif', '.bmp', '.tiff', '.tif'}
text_exts = {'.php', '.html', '.css', '.js', '.txt', '.sql', '.md', '.json'}

converted = []
for path in sorted(images_dir.iterdir()):
    if not path.is_file() or path.suffix.lower() not in exts:
        continue
    out = path.with_suffix('.webp')
    if not out.exists():
        with Image.open(path) as img:
            img.save(out, 'WEBP', quality=85)
        converted.append((path.name, out.name))

updated_files = []
for path in root.rglob('*'):
    if not path.is_file() or path.suffix.lower() not in text_exts:
        continue
    try:
        text = path.read_text(encoding='utf-8')
    except Exception:
        continue
    new_text = re.sub(r'(Imagenes/[^\s"\'()]+)\.(jpg|jpeg|png|gif|bmp|tiff|tif)(?=[^A-Za-z0-9_.-]|$)', r'\1.webp', text)
    if new_text != text:
        path.write_text(new_text, encoding='utf-8')
        updated_files.append(str(path.relative_to(root)).replace('\\', '/'))

print('Imágenes convertidas:')
for src, dst in converted:
    print(f'{src} -> {dst}')
print('Archivos actualizados:')
for item in updated_files:
    print(item)
