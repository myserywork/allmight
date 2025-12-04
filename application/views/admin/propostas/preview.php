<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - <?php echo $proposta->titulo; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .toolbar {
            background: #1e293b;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .toolbar h1 {
            font-size: 18px;
            font-weight: 600;
        }
        
        .toolbar-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #0ea5e9;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0284c7;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .btn-secondary {
            background: #64748b;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #475569;
        }
        
        .content {
            padding: 60px;
            min-height: 1000px;
        }
        
        .content img {
            max-width: 100%;
            height: auto;
        }
        
        .content table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .content table th,
        .content table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        .content table th {
            background: #f8fafc;
            font-weight: 600;
        }
        
        .content table tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .content h1 {
            color: #1e293b;
            margin: 30px 0 15px;
            font-size: 28px;
        }
        
        .content h2 {
            color: #334155;
            margin: 25px 0 12px;
            font-size: 22px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
        }
        
        .content h3 {
            color: #475569;
            margin: 20px 0 10px;
            font-size: 18px;
        }
        
        .content p {
            margin: 10px 0;
            text-align: justify;
        }
        
        .content ul, .content ol {
            margin: 10px 0 10px 30px;
        }
        
        .empty-state {
            text-align: center;
            padding: 100px 20px;
            color: #64748b;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .toolbar {
                display: none;
            }
            
            .container {
                box-shadow: none;
            }
            
            .content {
                padding: 40px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    
    <div class="container">
        <!-- Toolbar -->
        <div class="toolbar">
            <h1>
                <i class="fas fa-eye"></i>
                Preview da Proposta
            </h1>
            <div class="toolbar-actions">
                <a href="<?php echo base_url('admin/proposta/editar/' . $proposta->id); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Voltar
                </a>
                <a href="<?php echo base_url('admin/proposta/exportar/pdf/' . $proposta->id); ?>" class="btn btn-danger">
                    <i class="fas fa-file-pdf"></i>
                    Exportar PDF
                </a>
                <a href="<?php echo base_url('admin/proposta/exportar/docx/' . $proposta->id); ?>" class="btn btn-primary">
                    <i class="fas fa-file-word"></i>
                    Exportar DOCX
                </a>
                <button onclick="window.print()" class="btn btn-success">
                    <i class="fas fa-print"></i>
                    Imprimir
                </button>
            </div>
        </div>
        
        <!-- Conteúdo da Proposta -->
        <div class="content">
            <?php if (!empty($proposta->conteudo_html)): ?>
                <?php echo $proposta->conteudo_html; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-file-alt"></i>
                    <h2>Proposta sem conteúdo</h2>
                    <p>Esta proposta ainda não possui conteúdo gerado.</p>
                    <p>
                        <a href="<?php echo base_url('admin/proposta/editar/' . $proposta->id); ?>" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-edit"></i>
                            Editar Proposta
                        </a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
