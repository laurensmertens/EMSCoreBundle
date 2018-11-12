const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssEntryPlugin = require("css-entry-webpack-plugin");

module.exports = {
    plugins: [
        new MiniCssExtractPlugin({
            // Options similar to the same options in webpackOptions.output
            // both options are optional
            filename: "css/[name].bundle.css",
            chunkFilename: "[id].css"
        })
    ],
    context: path.resolve(__dirname, './'),
    entry: {
        'black': ['admin-lte/build/less/skins/skin-black.less'],
        'black-light': ['admin-lte/build/less/skins/skin-black-light.less'],
        'blue': ['admin-lte/build/less/skins/skin-blue.less'],
        'blue-light': ['admin-lte/build/less/skins/skin-blue-light.less'],
        'green': ['admin-lte/build/less/skins/skin-green.less'],
        'green-light': ['admin-lte/build/less/skins/skin-green-light.less'],
        'purple': ['admin-lte/build/less/skins/skin-purple.less'],
        'purple-light': ['admin-lte/build/less/skins/skin-purple-light.less'],
        'red': ['admin-lte/build/less/skins/skin-red.less'],
        'red-light': ['admin-lte/build/less/skins/skin-red-light.less'],
        'yellow': ['admin-lte/build/less/skins/skin-yellow.less'],
        'yellow-light': ['admin-lte/build/less/skins/skin-yellow-light.less'],
        'ems-app': ['@babel/polyfill', './Resources/assets/app.js'],
    },
    output: {
        path: path.resolve(__dirname, 'Resources/public'),
        filename: 'js/[name].bundle.js',
        //publicPath: '../bundles/emscore/',
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                loader: 'babel-loader'
            },
            {
                test: /\.less$/,
                use: [{
                    loader: MiniCssExtractPlugin.loader,
                    options: {
                        // you can specify a publicPath here
                        // by default it use publicPath in webpackOptions.output
                        publicPath: '../'
                    }
                },{
                    loader: 'css-loader', // translates CSS into CommonJS
                    options: {
                        sourceMap: true
                    }
                }, {
                    loader: 'less-loader' // compiles Less to CSS
                }]
            },
            {
                test: /\.(sa|sc|c)ss$/,
                use: [{
                        loader: MiniCssExtractPlugin.loader,
                        options: {
                            // you can specify a publicPath here
                            // by default it use publicPath in webpackOptions.output
                            publicPath: '../'
                        }
                    },{
                        loader: 'css-loader',
                        options: {
                            sourceMap: true
                        }
                    },
                    // 'postcss-loader',
                    'sass-loader',
                ],
            },
            {
                test: /\.(png|jpg|gif|svg|eot|ttf|woff|woff2)$/,
                loader: 'url-loader',
                options: {
                    limit: 10000,
                    name: 'media/[name].[ext]',
                }
            }
        ]
    }
};