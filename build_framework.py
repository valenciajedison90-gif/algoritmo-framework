#!/usr/bin/env python3
"""
Script de Construcción y Empaquetado de Algoritmo Framework.
Uso: python build_framework.py
"""

import os
import sys
import zipfile
from pathlib import Path

# Configurar encoding UTF-8 en stdout si es posible en Windows
if sys.platform == "win32" and hasattr(sys.stdout, "reconfigure"):
    try:
        sys.stdout.reconfigure(encoding="utf-8")
    except Exception:
        pass

# Definición de carpetas obligatorias del Core
ESTRUCTURA_DIRECTORIOS = [
    ".claude/rules",
    ".claude/agents",
    ".claude/skills",
    "app/ADO",
    "app/BLL",
    "app/DAL",
    "app/Core",
    "app/Core/Helpers",
    "app/Http/Controllers/Auth",
    "app/Http/Requests",
    "config",
    "database/migrations",
    "database/seeders",
    "docs",
    "resources/views/auth",
    "resources/views/empresas",
    "resources/views/usuarios",
    "resources/views/layouts",
    "resources/views/dashboard",
    "routes",
    "src",
    "stubs",
]

# Exclusiones estrictas que nunca deben empaquetarse
EXCLUSIONES = {
    "vendor",
    "node_modules",
    "storage",
    "bootstrap",
    "public",
    ".git",
    "__pycache__",
    ".DS_Store",
    "Thumbs.db",
    "algoritmo-framework.zip",
}

def crear_estructura(base_dir: Path):
    """Crea los directorios esenciales si no existen."""
    print("==> [1/3] Verificando y creando estructura de directorios...")
    for subpath in ESTRUCTURA_DIRECTORIOS:
        directorio = base_dir / subpath
        directorio.mkdir(parents=True, exist_ok=True)
    print("    [OK] Estructura de carpetas verificada.")

def verificar_archivos_criticos(base_dir: Path):
    """Verifica la existencia de archivos indispensables del framework."""
    print("==> [2/3] Verificando archivos criticos del Core...")
    archivos_criticos = [
        "install.php",
        "VERSION",
        "README.md",
        "docs/analisis-legado.md",
        "docs/arquitectura.md",
        "docs/INSTALACION.md",
        "src/Installer.php",
        "src/Console.php",
        "src/Filesystem.php",
        "src/Composer.php",
        "src/MergeConfig.php",
        "app/Core/ResponseHelper.php",
        "app/Core/BusinessException.php",
        "app/Core/SecurityService.php",
        "app/Core/AuditService.php",
        "app/Core/PermissionService.php",
        "app/Core/MailService.php",
        "app/Core/LogService.php",
        "app/ADO/ADOEmpresa.php",
        "app/BLL/EmpresaBLL.php",
        "app/DAL/EmpresaDAL.php",
        "app/ADO/ADOUsuario.php",
        "app/BLL/UsuarioBLL.php",
        "app/DAL/UsuarioDAL.php",
        "app/ADO/ADOLogin.php",
        "app/BLL/LoginBLL.php",
        "app/DAL/LoginDAL.php",
        "routes/web.php",
    ]

    faltantes = []
    for rel_path in archivos_criticos:
        if not (base_dir / rel_path).exists():
            faltantes.append(rel_path)

    if faltantes:
        print("    [ERROR] Faltan archivos criticos en el Core:")
        for f in faltantes:
            print(f"      - {f}")
        sys.exit(1)

    print("    [OK] Todos los archivos criticos del Core estan presentes.")

def comprimir_framework(base_dir: Path, output_zip: Path):
    """Empaqueta el repositorio en algoritmo-framework.zip excluyendo artefactos ajenos."""
    print(f"==> [3/3] Comprimiendo el Core en {output_zip.name}...")
    archivos_agregados = 0

    with zipfile.ZipFile(output_zip, "w", zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk(base_dir):
            dirs[:] = [d for d in dirs if d not in EXCLUSIONES and not d.startswith(".git")]

            for file in files:
                if file in EXCLUSIONES or file.endswith(".zip") or file.endswith(".pyc"):
                    continue

                abs_file = Path(root) / file
                rel_path = abs_file.relative_to(base_dir)

                if any(part in EXCLUSIONES for part in rel_path.parts):
                    continue

                zipf.write(abs_file, arcname=str(rel_path))
                archivos_agregados += 1

    tamano_kb = output_zip.stat().st_size / 1024
    print(f"    [OK] Empaquetado completado: {archivos_agregados} archivos ({tamano_kb:.2f} KB).")
    print(f"    [OK] Archivo generado: {output_zip.resolve()}")

def main():
    print("====================================================================")
    print("            ALGORITMO FRAMEWORK - BUILDER & PACKAGER               ")
    print("====================================================================")
    base_dir = Path(__file__).resolve().parent

    crear_estructura(base_dir)
    verificar_archivos_criticos(base_dir)

    output_zip = base_dir / "algoritmo-framework.zip"
    comprimir_framework(base_dir, output_zip)

    print("====================================================================")
    print("  Construccion finalizada con exito!")
    print("====================================================================")

if __name__ == "__main__":
    main()
