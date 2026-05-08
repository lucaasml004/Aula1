module.exports = (roles = []) => {
    return (req, res, next) => {
        if (!req.session.userId) {
            return res.redirect('/login');
        }

        if (roles.length && !roles.includes(req.session.perfil)) {
            return res.status(403).send('Acesso negado.');
        }

        next();
    };
};
